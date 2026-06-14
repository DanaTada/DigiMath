<?php
// ai_helper.php — funkcijas darbam ar Claude (Haiku)

// --- Zema līmeņa izsaukums: nosūta promptu, atgriež teksta atbildi ---
function claude_request($prompt, $max_tokens = 600, $temperature = 1) {
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
            'model'       => 'claude-haiku-4-5-20251001',
            'max_tokens'  => $max_tokens,
            'temperature' => $temperature,
            'messages'    => [['role' => 'user', 'content' => $prompt]],
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

// --- PHP ģenerē uzdevumu pēc tipa (droši, bez AI; bezgalīgi varianti) ---
function genere_php($tips) {
    switch ($tips) {

        case 'plus': // 1. klase saskaitīšana
            $a = rand(1, 9); $b = rand(1, 9);
            return ["$a + $b =", (string)($a + $b)];

        case 'minus': // 1. klase atņemšana
            $a = rand(2, 10); $b = rand(1, $a);
            return ["$a - $b =", (string)($a - $b)];

        case 'plus2': // 2. klase līdz 100
            $a = rand(10, 80); $b = rand(10, 99 - $a > 10 ? 99 - $a : 10);
            return ["$a + $b =", (string)($a + $b)];

        case 'reiz': // reizināšanas tabula / reizināšana
            $a = rand(2, 12); $b = rand(2, 12);
            return ["$a × $b =", (string)($a * $b)];

        case 'reiz2': // 4. klase reizināšana stabiņā
            $a = rand(12, 99); $b = rand(2, 9);
            return ["$a × $b =", (string)($a * $b)];

        case 'dal': // dalīšana bez atlikuma
            $b = rand(2, 12); $atb = rand(2, 12);
            $a = $b * $atb;
            return ["$a ÷ $b =", (string)$atb];

        case 'dalskaitli': // daļskaitļu saskaitīšana ar vienādu saucēju
            $sauc = rand(3, 9);
            $a = rand(1, $sauc - 2); $b = rand(1, $sauc - $a - 1);
            $sk = $a + $b;
            // vienkāršošana
            $g = gcd($sk, $sauc);
            $rez = ($sauc / $g == 1) ? (string)($sk / $g) : ($sk / $g) . "/" . ($sauc / $g);
            return ["$a/$sauc + $b/$sauc =", $rez];

        case 'decimal': // decimāldaļu saskaitīšana (komats)
            $a = rand(10, 99) / 10; $b = rand(10, 99) / 10;
            $rez = $a + $b;
            $fmt = fn($x) => rtrim(rtrim(number_format($x, 1, ',', ''), '0'), ',');
            return [$fmt($a) . " + " . $fmt($b) . " =", $fmt($rez)];

        case 'procenti': // cik ir X% no Y
            $proc = [10, 20, 25, 50][array_rand([10, 20, 25, 50])];
            $skaitlis = rand(1, 20) * ($proc == 25 ? 4 : ($proc == 50 ? 2 : 10));
            return ["Cik ir $proc% no $skaitlis?", (string)($skaitlis * $proc / 100)];

        case 'veselie': // pozitīvie/negatīvie
            $a = rand(-10, 10); $b = rand(-10, 10);
            $bb = $b < 0 ? "($b)" : "$b";
            return ["$a + $bb =", (string)($a + $b)];

        case 'vienadojums': // ax + b = c
            $x = rand(2, 9); $a = rand(2, 6); $b = rand(1, 9);
            $c = $a * $x + $b;
            return ["{$a}x + $b = $c|x = ?", (string)$x];

        case 'pakape': // a^n
            $a = rand(2, 6); $n = rand(2, 4);
            return ["$a^$n =", (string)pow($a, $n)];

        case 'pitagors': // pitagora trijnieki
            $trijnieki = [[3,4,5],[6,8,10],[5,12,13],[8,15,17],[9,12,15],[7,24,25],[20,21,29]];
            $t = $trijnieki[array_rand($trijnieki)];
            return ["Katetes ir {$t[0]} un {$t[1]}. Hipotenūza?", (string)$t[2]];

        case 'sakne': // kvadrātsakne no pilna kvadrāta
            $n = rand(2, 15);
            return ["√" . ($n * $n) . " =", (string)$n];

        case 'procentu_uzd': // cena +/- procenti
            $cena = rand(2, 10) * 10;
            $proc = [10, 20, 25, 50][array_rand([10, 20, 25, 50])];
            if (rand(0, 1)) {
                return ["Prece maksā $cena €. To sadārdzina par $proc%. Jaunā cena (€)?", (string)($cena + $cena * $proc / 100)];
            }
            return ["Prece maksā $cena €, atlaide $proc%. Jaunā cena (€)?", (string)($cena - $cena * $proc / 100)];

        case 'progresija': // aritmētiskā progresija
            $sak = rand(1, 9); $solis = rand(2, 6); $n = rand(4, 6);
            $virkne = [];
            for ($i = 0; $i < 3; $i++) $virkne[] = $sak + $i * $solis;
            $loceklis = $sak + ($n - 1) * $solis;
            return ["Progresija: " . implode(", ", $virkne) . "... Kāds ir $n. loceklis?", (string)$loceklis];
    }
    return null;
}

// Lielākais kopīgais dalītājs (daļskaitļu vienkāršošanai)
function gcd($a, $b) { return $b == 0 ? $a : gcd($b, $a % $b); }

// --- Ģenerē jaunu uzdevumu: vispirms ar PHP (pēc tips), citādi ar AI / paraugu ---
function genere_uzdevumu($tema_label, $klase, $piemeri, $tips = null) {
    // 1) Galvenais ceļš: PHP ģenerators (drošs, bezgalīgi varianti)
    if ($tips) {
        $php = genere_php($tips);
        if ($php) {
            return ['uzdevums' => $php[0], 'atbilde' => $php[1]];
        }
    }

    // 2) Ja tips nav zināms — AI ģenerē uz paraugu bāzes
    $saraksts = '';
    foreach ($piemeri as $p) {
        $saraksts .= "- " . $p['uzdevums'] . " (atbilde: " . $p['atbilde'] . ")\n";
    }
    $prompt = "Šeit ir paraugi uzdevumiem tēmā \"$tema_label\" ($klase. klase):\n"
        . $saraksts
        . "Izveido VIENU JAUNU līdzīgu uzdevumu ar CITIEM skaitļiem (variācija: " . rand(1000, 9999) . "). "
        . "Atbildei jābūt viennozīmīgai. Saglabā to pašu pieraksta stilu kā paraugos. "
        . "Atbildi TIKAI ar JSON, bez markdown: {\"uzdevums\":\"...\",\"atbilde\":\"...\"}";

    $teksts = claude_request($prompt, 250, 1);
    $dati = izvelkties_json($teksts);
    if ($dati && isset($dati['uzdevums'], $dati['atbilde'])) {
        return $dati;
    }
    // 3) Pēdējā rezerve: nejaušs paraugs no JSON
    return $piemeri[array_rand($piemeri)];
}

// --- AI paskaidro, kāpēc atbilde ir pareiza vai nepareiza (stils pēc klases) ---
function paskaidro_atbildi($uzdevums, $pareiza, $skolena, $klase = 1) {
    if (trim($skolena) === '') {
        $statuss = "Skolēns vēl nav ievadījis atbildi.";
    } elseif (trim($skolena) === trim($pareiza)) {
        $statuss = "Skolēns atbildēja PAREIZI.";
    } else {
        $statuss = "Skolēns atbildēja NEPAREIZI.";
    }

    // Stils atkarībā no klases grupas
    $klase = (int)$klase;
    if ($klase >= 1 && $klase <= 3) {
        $stils = "Skolēns ir 1.-3. klasē (apmēram 7-9 gadi). "
            . "Runā ļoti vienkārši un draudzīgi, kā ar mazu bērnu. "
            . "Lieto īsus teikumus un ikdienišķus piemērus (āboli, konfektes, pirksti). "
            . "Izvairies no sarežģītiem terminiem.";
    } elseif ($klase >= 4 && $klase <= 6) {
        $stils = "Skolēns ir 4.-6. klasē (apmēram 10-12 gadi). "
            . "Paskaidro skaidri un soli pa solim, draudzīgā tonī. "
            . "Drīksti lietot vienkāršu matemātisko terminoloģiju un parādīt risinājuma gaitu.";
    } else {
        $stils = "Skolēns ir 7.-9. klasē (apmēram 13-15 gadi). "
            . "Runā cieņpilni un lietišķi, kā ar pusaudzi, BEZ bērnišķīga toņa. "
            . "Lieto pareizu matemātisko terminoloģiju, esi konkrēts un kodolīgs, "
            . "skaidro loģiku aiz risinājuma, nevis tikai rezultātu.";
    }

    $prompt = "Tu esi matemātikas skolotājs. $stils\n"
        . "Atbildi latviešu valodā. $statuss\n"
        . "Uzdevums: $uzdevums\n"
        . "Pareizā atbilde: $pareiza\n"
        . "Skolēna atbilde: $skolena\n\n"
        . "Galvenais: parādi RISINĀJUMA GAITU soli pa solim, nevis garu skaidrojumu vārdiem. "
        . "Vispirms 1-2 īsi teikumi par to, kā risināt, pēc tam soļu ķēde, piem.: "
        . "6x - 4 = 20  ->  6x = 20 + 4  ->  6x = 24  ->  x = 24/6  ->  x = 4\n"
        . "Katru soli raksti jaunā rindā ar bultiņu ->. "
        . "Ja atbilde nepareiza - īsi norādi, kurā solī skolēns kļūdījās. "
        . "Ja pareiza - apstiprini un parādi pilnu risinājuma gaitu. "
        . "Neraksti markdown, neraksti lieku tekstu.";

    return claude_request($prompt, 500, 0.3);
}