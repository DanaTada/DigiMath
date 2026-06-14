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
// --- Ģenerē līdzīgu uzdevumu (pilnīgi nejauši, bez AI) ---
function genere_lidzigu_uzdevumu($templateTask) {
    $taskText = $templateTask['text'];
    $taskGrade = $templateTask['grade'];
    
    // ==================== SPECIAL CASE: NEGATIVE NUMBERS (a - (-b)) ====================
    // Check for pattern like "5 - (-3)" or "x - (-y)"
    if (preg_match('/\d+\s*-\s*\(\s*-\s*\d+\s*\)/', $taskText)) {
        // Generate similar: a - (-b) where a and b are positive
        if ($taskGrade <= 4) {
            $a = rand(5, 20);
            $b = rand(1, 10);
        } elseif ($taskGrade <= 6) {
            $a = rand(10, 50);
            $b = rand(5, 20);
        } else {
            $a = rand(20, 100);
            $b = rand(10, 50);
        }
        $result = $a + $b; // a - (-b) = a + b
        return [
            'uzdevums' => "$a - (-$b) =",
            'atbilde' => (string)$result
        ];
    }
    
    // Also check for "x - (-y)" with variables
    if (preg_match('/[a-z]\s*-\s*\(\s*-\s*\d+\s*\)/', $taskText)) {
        $b = rand(2, 15);
        return [
            'uzdevums' => "x - (-$b) =",
            'atbilde' => "x + $b"
        ];
    }
    
    // ==================== DETECT ALL TASK TYPES ====================
    
    // FRACTIONS
    $hasFraction = preg_match('/\d+\/\d+/', $taskText);
    $hasFractionAddition = strpos($taskText, '+') !== false && $hasFraction;
    $hasFractionSubtraction = strpos($taskText, '-') !== false && $hasFraction;
    
    // PERCENTAGES
    $isPercentage = strpos($taskText, '%') !== false;
    
    // QUADRATIC EQUATIONS (x²)
    $isQuadratic = strpos($taskText, 'x²') !== false || strpos($taskText, 'x^2') !== false;
    
    // LINEAR EQUATIONS (ax + b = c or ax = b)
    $isLinearEquation = (strpos($taskText, 'x') !== false && !$isQuadratic && strpos($taskText, '=') !== false);
    
    // SYSTEMS OF EQUATIONS
    $isSystemOfEquations = (substr_count($taskText, 'x') >= 2 || substr_count($taskText, '=') >= 2) && strpos($taskText, ';') !== false;
    
    // TRIGONOMETRY
    $isTrigonometry = strpos($taskText, 'sin') !== false || strpos($taskText, 'cos') !== false || strpos($taskText, 'tan') !== false;
    
    // SQUARE ROOTS
    $isSquareRoot = strpos($taskText, '√') !== false || strpos($taskText, 'sqrt') !== false;
    
    // POWERS
    $hasPowers = preg_match('/\d²|\d³|x²|x³/', $taskText);
    
    // DECIMALS
    $hasDecimals = preg_match('/\d+\.\d+/', $taskText);
    
    // WORD PROBLEMS
    $isWordProblem = strlen($taskText) > 60 && (strpos($taskText, 'cik') !== false || strpos($taskText, 'Cik') !== false);
    
    // NEGATIVE NUMBERS in addition/subtraction (like -5 + 3)
    $hasNegativeNumbers = preg_match('/-\d+\s*[\+\-]/', $taskText) && !preg_match('/\(\s*-\s*\d+\s*\)/', $taskText);
    
    // Basic operations
    $isAddition = strpos($taskText, '+') !== false && !$hasFraction && !$hasDecimals && !$hasNegativeNumbers;
    $isSubtraction = strpos($taskText, '-') !== false && !$hasFraction && !$hasDecimals && !preg_match('/\(\s*-\s*\d+\s*\)/', $taskText);
    $isMultiplication = (strpos($taskText, '×') !== false || strpos($taskText, '*') !== false) && !$hasFraction;
    $isDivision = (strpos($taskText, '÷') !== false || strpos($taskText, '/') !== false) && !$hasFraction;
    
    // ==================== NEGATIVE NUMBERS (like -5 + 3) ====================
    if ($hasNegativeNumbers) {
        if (strpos($taskText, '+') !== false) {
            // Example: -5 + 3 = -2
            $a = rand(5, 30);
            $b = rand(1, $a);
            $result = -$a + $b;
            return [
                'uzdevums' => "-$a + $b =",
                'atbilde' => (string)$result
            ];
        } elseif (strpos($taskText, '-') !== false) {
            // Example: -5 - 3 = -8
            $a = rand(5, 30);
            $b = rand(1, 20);
            $result = -$a - $b;
            return [
                'uzdevums' => "-$a - $b =",
                'atbilde' => (string)$result
            ];
        }
    }
    
    // ==================== FRACTIONS ====================
    if ($hasFractionAddition || $hasFractionSubtraction) {
        if ($taskGrade <= 4) {
            $denom1 = rand(2, 4);
            $denom2 = rand(2, 4);
            $num1 = rand(1, $denom1 - 1);
            $num2 = rand(1, $denom2 - 1);
        } else {
            $denom1 = rand(2, 8);
            $denom2 = rand(2, 8);
            $num1 = rand(1, $denom1 - 1);
            $num2 = rand(1, $denom2 - 1);
        }
        
        $commonDenom = $denom1 * $denom2;
        $newNum1 = $num1 * $denom2;
        $newNum2 = $num2 * $denom1;
        
        if ($hasFractionAddition) {
            $resultNum = $newNum1 + $newNum2;
            $operation = '+';
        } else {
            $resultNum = $newNum1 - $newNum2;
            $operation = '-';
        }
        
        $gcd = function($a, $b) use (&$gcd) {
            return ($b == 0) ? $a : $gcd($b, $a % $b);
        };
        $divisor = $gcd(abs($resultNum), $commonDenom);
        $resultNumSimplified = $resultNum / $divisor;
        $commonDenomSimplified = $commonDenom / $divisor;
        
        if ($commonDenomSimplified == 1) {
            $answer = (string)$resultNumSimplified;
        } elseif ($resultNumSimplified == 0) {
            $answer = "0";
        } else {
            $answer = "{$resultNumSimplified}/{$commonDenomSimplified}";
        }
        
        return [
            'uzdevums' => "{$num1}/{$denom1} {$operation} {$num2}/{$denom2} =",
            'atbilde' => $answer
        ];
    }
    
    // ==================== PERCENTAGES ====================
    if ($isPercentage) {
        $percent = rand(10, 50);
        $number = rand(50, 200);
        return [
            'uzdevums' => "$percent% no $number =",
            'atbilde' => (string)(($percent / 100) * $number)
        ];
    }
    
    // ==================== SYSTEMS OF EQUATIONS ====================
    if ($isSystemOfEquations) {
        $x = rand(2, 8);
        $y = rand(2, 8);
        $sum = $x + $y;
        $diff = $x - $y;
        return [
            'uzdevums' => "x + y = {$sum}; x - y = {$diff} (x = ?)",
            'atbilde' => (string)$x
        ];
    }
    
    // ==================== QUADRATIC EQUATIONS ====================
    if ($isQuadratic) {
        $root1 = rand(2, 8);
        $root2 = rand(2, 8);
        $sum = $root1 + $root2;
        $product = $root1 * $root2;
        return [
            'uzdevums' => "x² - {$sum}x + {$product} = 0 (x = ?)",
            'atbilde' => "$root1,$root2"
        ];
    }
    
    // ==================== LINEAR EQUATIONS ====================
    if ($isLinearEquation) {
        if (strpos($taskText, '+') !== false || strpos($taskText, '-') !== false) {
            $a = rand(2, 5);
            $b = rand(5, 30);
            $x = rand(3, 10);
            $c = ($a * $x) + $b;
            return [
                'uzdevums' => "{$a}x + {$b} = {$c} (x = ?)",
                'atbilde' => (string)$x
            ];
        } else {
            $a = rand(2, 5);
            $x = rand(3, 10);
            $b = $a * $x;
            return [
                'uzdevums' => "{$a}x = {$b} (x = ?)",
                'atbilde' => (string)$x
            ];
        }
    }
    
    // ==================== TRIGONOMETRY ====================
    if ($isTrigonometry) {
        $angles = [0, 30, 45, 60, 90];
        $angle = $angles[array_rand($angles)];
        
        if (strpos($taskText, 'sin') !== false) {
            $value = sin(deg2rad($angle));
            $value = round($value, 1);
            return [
                'uzdevums' => "sin {$angle}° = ?",
                'atbilde' => (string)$value
            ];
        } elseif (strpos($taskText, 'cos') !== false) {
            $value = cos(deg2rad($angle));
            $value = round($value, 1);
            return [
                'uzdevums' => "cos {$angle}° = ?",
                'atbilde' => (string)$value
            ];
        } elseif (strpos($taskText, 'tan') !== false) {
            $value = tan(deg2rad($angle));
            $value = round($value, 1);
            return [
                'uzdevums' => "tan {$angle}° = ?",
                'atbilde' => (string)$value
            ];
        }
    }
    
    // ==================== SQUARE ROOTS ====================
    if ($isSquareRoot) {
        $perfectSquares = [4, 9, 16, 25, 36, 49, 64, 81, 100];
        $number = $perfectSquares[array_rand($perfectSquares)];
        $answer = sqrt($number);
        return [
            'uzdevums' => "√{$number} = ?",
            'atbilde' => (string)$answer
        ];
    }
    
    // ==================== POWERS ====================
    if ($hasPowers) {
        $base = rand(2, 12);
        if (strpos($taskText, '²') !== false) {
            return [
                'uzdevums' => "{$base}² = ?",
                'atbilde' => (string)($base * $base)
            ];
        } elseif (strpos($taskText, '³') !== false) {
            return [
                'uzdevums' => "{$base}³ = ?",
                'atbilde' => (string)($base * $base * $base)
            ];
        }
    }
    
    // ==================== DECIMALS ====================
    if ($hasDecimals) {
        if (strpos($taskText, '+') !== false) {
            $a = rand(10, 99) / 10;
            $b = rand(10, 99) / 10;
            return [
                'uzdevums' => "$a + $b =",
                'atbilde' => (string)($a + $b)
            ];
        } elseif (strpos($taskText, '-') !== false) {
            $a = rand(10, 99) / 10;
            $b = rand(10, $a*10) / 10;
            return [
                'uzdevums' => "$a - $b =",
                'atbilde' => (string)($a - $b)
            ];
        }
    }
    
    // ==================== WORD PROBLEMS ====================
    if ($isWordProblem) {
        $num1 = rand(10, 50);
        $num2 = rand(5, 30);
        $operations = ['+', '-', '×', '÷'];
        $op = $operations[array_rand($operations)];
        
        if ($op == '+') {
            return ['uzdevums' => "Cik ir {$num1} plus {$num2}?", 'atbilde' => (string)($num1 + $num2)];
        } elseif ($op == '-') {
            return ['uzdevums' => "Cik ir {$num1} mīnus {$num2}?", 'atbilde' => (string)($num1 - $num2)];
        } elseif ($op == '×') {
            return ['uzdevums' => "Cik ir {$num1} reiz {$num2}?", 'atbilde' => (string)($num1 * $num2)];
        } else {
            $result = round($num1 / $num2, 1);
            return ['uzdevums' => "Cik ir {$num1} dalīts ar {$num2}?", 'atbilde' => (string)$result];
        }
    }
    
    // ==================== BASIC ADDITION ====================
    if ($isAddition) {
        if ($taskGrade <= 2) {
            $a = rand(1, 20);
            $b = rand(1, 20);
        } elseif ($taskGrade <= 4) {
            $a = rand(10, 100);
            $b = rand(10, 100);
        } elseif ($taskGrade <= 6) {
            $a = rand(50, 500);
            $b = rand(50, 500);
        } else {
            $a = rand(100, 1000);
            $b = rand(100, 1000);
        }
        return [
            'uzdevums' => "$a + $b =",
            'atbilde' => (string)($a + $b)
        ];
    }
    
    // ==================== BASIC SUBTRACTION ====================
    if ($isSubtraction) {
        if ($taskGrade <= 2) {
            $a = rand(10, 20);
            $b = rand(2, $a);
        } elseif ($taskGrade <= 4) {
            $a = rand(30, 100);
            $b = rand(5, $a);
        } elseif ($taskGrade <= 6) {
            $a = rand(100, 500);
            $b = rand(20, $a);
        } else {
            $a = rand(200, 1000);
            $b = rand(50, $a);
        }
        return [
            'uzdevums' => "$a - $b =",
            'atbilde' => (string)($a - $b)
        ];
    }
    
    // ==================== BASIC MULTIPLICATION ====================
    if ($isMultiplication) {
        if ($taskGrade <= 3) {
            $a = rand(2, 5);
            $b = rand(2, 5);
        } elseif ($taskGrade <= 5) {
            $a = rand(3, 10);
            $b = rand(3, 10);
        } else {
            $a = rand(5, 20);
            $b = rand(4, 12);
        }
        return [
            'uzdevums' => "$a × $b =",
            'atbilde' => (string)($a * $b)
        ];
    }
    
    // ==================== BASIC DIVISION ====================
    if ($isDivision) {
        if ($taskGrade <= 4) {
            $divisor = rand(2, 5);
            $result = rand(2, 10);
        } else {
            $divisor = rand(3, 12);
            $result = rand(3, 15);
        }
        $dividend = $divisor * $result;
        return [
            'uzdevums' => "$dividend ÷ $divisor =",
            'atbilde' => (string)$result
        ];
    }
    
    // ==================== DEFAULT FALLBACK ====================
    if ($taskGrade <= 2) {
        $a = rand(5, 20);
        $b = rand(5, 20);
    } elseif ($taskGrade <= 4) {
        $a = rand(20, 100);
        $b = rand(20, 100);
    } else {
        $a = rand(50, 500);
        $b = rand(50, 500);
    }
    return [
        'uzdevums' => "$a + $b =",
        'atbilde' => (string)($a + $b)
    ];
}
?>