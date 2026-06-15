<?php
// ai_helper.php — tikai AI, bez lokālās ģenerēšanas

// --- Zema līmeņa izsaukums: nosūta promptu, atgriež teksta atbildi ---
function claude_request($prompt, $max_tokens = 600) {
    require __DIR__ . '/config.php';

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
        return 'Kļūda: ' . $err;
    }
    curl_close($ch);

    $response = json_decode($raw, true);
    return $response['content'][0]['text'] ?? 'Kļūda';
}

// --- Izvelk JSON no modeļa atbildes ---
function izvelkties_json($text) {
    $s = strpos($text, '{');
    $e = strrpos($text, '}');
    if ($s === false || $e === false) return null;
    return json_decode(substr($text, $s, $e - $s + 1), true);
}

// --- AI paskaidrojums ---
function paskaidro_atbildi($uzdevums, $pareiza, $skolena) {
    if (trim($skolena) === '') {
        $statuss = "Skolēns vēl nav ievadījis atbildi.";
    } elseif (trim($skolena) === trim($pareiza)) {
        $statuss = "Skolēns atbildēja PAREIZI.";
    } else {
        $statuss = "Skolēns atbildēja NEPAREIZI.";
    }

    $prompt = "Tu esi draudzīgs matemātikas skolotājs. Paskaidro latviski vienkārši. "
        . "$statuss\nUzdevums: $uzdevums\nPareizā atbilde: $pareiza\nSkolēna atbilde: $skolena\n"
        . "Ja pareizi - paslavē. Ja nepareizi - paskaidro kļūdu.";

    return claude_request($prompt, 500);
}
// --- AI ģenerē līdzīgu uzdevumu pēc parauga ---
function genere_lidzigu_uzdevumu($templateTask) {
    $taskText = $templateTask['text'];
    $taskAnswer = $templateTask['atbilde'];
    $taskGrade = $templateTask['grade'];
    
    // Force randomness with multiple seeds
    $seed1 = rand(1, 10);
    $seed2 = rand(1, 10);
    $seed3 = rand(1, 10);
    $targetNumber = rand(10, 40);
    
    $prompt = "UZMANĪBU! ŠIS IR JAUNS UN UNIKĀLS PIEPRASĪJUMS!
ID: {$seed1}-{$seed2}-{$seed3}

Uzraksti VIENU matemātikas uzdevumu {$taskGrade}. klasei.

PARAUGS (NEKOPĒ, IZMANTO TIKAI KĀ IDEJU): 
\"{$taskText}\" (atbilde: \"{$taskAnswer}\")

OBLIGĀTI JĀIEVĒRO:
1. Uzdevuma TIPS paliek tāds pats (salīdzināšana, saskaitīšana, daļas, utt.)
2. VISI skaitļi JĀBŪT CITIEM! NEVIENU skaitli nedrīkst atkārtot!
3. IZMANTO ŠO SKAITLI kā bāzi: {$targetNumber}
4. Atbildes FORMATS paliek tāds pats kā paraugā

PIEMĒRS, KĀ DARĪT (nevis ko kopēt):
Ja paraugs ir \"3h 12min = ? min\" (atbilde: \"192\")
Tu vari uzrakstīt \"5h 45min = ? min\" (atbilde: \"345\")
NEVIS \"3h 12min\" atkal!

TAGAD UZRAKSTI SAVU UNIKĀLO UZDEVUMU!

ATBILDI TIKAI AR JSON: {\"uzdevums\":\"...\",\"atbilde\":\"...\"}";

    $teksts = claude_request($prompt, 500);
    $dati = izvelkties_json($teksts);
    
    if ($dati && isset($dati['uzdevums'], $dati['atbilde'])) {
        return $dati;
    }
    
    // Fallback with random numbers
    $randomNum1 = rand(1, 100);
    $randomNum2 = rand(1, 100);
    return [
        'uzdevums' => str_replace(explode(' ', $taskText)[0], $randomNum1, $taskText),
        'atbilde' => $taskAnswer
    ];
}