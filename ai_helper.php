<?php
// ai_helper.php — funkcijas darbam ar Claude (Haiku)

// --- Zema līmeņa izsaukums: nosūta promptu, atgriež teksta atbildi ---
function claude_request($prompt, $max_tokens = 600) {
    require __DIR__ . '/config.php'; // $apiKey

    $ch = curl_init('https://api.anthropic.com/v1/messages');
    curl_setopt_array($ch, [
        CURLOPT_HTTPHEADER => [
            'x-api-key: ' . $apiKey,
            'anthropic-version: 2023-06-01',
            'content-type: application/json',
        ],
        CURLOPT_POST           => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 30,
        CURLOPT_POSTFIELDS     => json_encode([
            'model'      => 'claude-haiku-4-5-20251001',
            'max_tokens' => $max_tokens,
            'messages'   => [['role' => 'user', 'content' => $prompt]],
        ]),
    ]);

    $raw = curl_exec($ch);
    if ($raw === false) {
        $err = curl_error($ch);
        curl_close($ch);
        return 'Kļūda savienojumā: ' . $err;
    }
    curl_close($ch);

    $response = json_decode($raw, true);
    return $response['content'][0]['text'] ?? ('Kļūda: ' . json_encode($response));
}

// --- Izvelk JSON no modeļa atbildes ---
function izvelkties_json($text) {
    $s = strpos($text, '{');
    $e = strrpos($text, '}');
    if ($s === false || $e === false) return null;
    return json_decode(substr($text, $s, $e - $s + 1), true);
}

// --- Rezerves variants, ja AI neatbild: ģenerē uzdevumu lokāli ---
function genere_lokali($tema) {
    if ($tema === 'minus') {
        $a = rand(2, 10); $b = rand(1, $a);
        return ['uzdevums' => "$a - $b =", 'atbilde' => (string)($a - $b)];
    }
    $a = rand(1, 9); $b = rand(1, 9);
    return ['uzdevums' => "$a + $b =", 'atbilde' => (string)($a + $b)];
}

// --- AI ģenerē jaunu uzdevumu pēc tēmas (plus / minus) ---
function genere_uzdevums($tema) {
    if ($tema === 'minus') {
        $apraksts = "atņemšanas uzdevumu (piemēram, 7 - 2 =). Rezultāts nedrīkst būt negatīvs";
    } else {
        $apraksts = "saskaitīšanas uzdevumu (piemēram, 3 + 1 =)";
    }
    $prompt = "Izveido vienu vienkāršu $apraksts 1. klases skolēnam. "
        . "Skaitļi no 1 līdz 10. "
        . "Atbildi TIKAI ar JSON, bez paskaidrojumiem un bez markdown: "
        . '{"uzdevums":"3 + 1 =","atbilde":"4"}';

    $teksts = claude_request($prompt, 200);
    $dati = izvelkties_json($teksts);
    if ($dati && isset($dati['uzdevums'], $dati['atbilde'])) {
        return $dati;
    }
    return genere_lokali($tema); // ja AI neatbildēja korekti
}

// --- AI paskaidro, kāpēc atbilde ir pareiza vai nepareiza ---
function paskaidro_atbildi($uzdevums, $pareiza, $skolena) {
    if (trim($skolena) === '') {
        $statuss = "Skolēns vēl nav ievadījis atbildi.";
    } elseif (trim($skolena) === trim($pareiza)) {
        $statuss = "Skolēns atbildēja PAREIZI.";
    } else {
        $statuss = "Skolēns atbildēja NEPAREIZI.";
    }

    $prompt = "Tu esi draudzīgs 1. klases matemātikas skolotājs. "
        . "Paskaidro latviešu valodā ļoti vienkārši un draudzīgi (bērnam 7 gadu vecumā). "
        . "$statuss\n"
        . "Uzdevums: $uzdevums\n"
        . "Pareizā atbilde: $pareiza\n"
        . "Skolēna atbilde: $skolena\n"
        . "Ja pareizi - īsi paslavē un pasaki, kāpēc tas ir pareizi. "
        . "Ja nepareizi - maigi paskaidro, kur ir kļūda un kā nonākt līdz pareizai atbildei. "
        . "Neraksti markdown.";

    return claude_request($prompt, 500);
}