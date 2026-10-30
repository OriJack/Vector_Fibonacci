<?php
header('Content-Type: application/json; charset=utf-8');

$data = json_decode(file_get_contents('php://input'), true);
if (!isset($data['vector']) || !is_array($data['vector']) || count($data['vector']) === 0) {
    echo json_encode(['error' => 'Vector vacío o inválido.']);
    exit;
}

$vector = array_map('intval', $data['vector']);
$n = count($vector);

function esPrimo(int $num): bool
{
    if ($num < 2)
        return false;
    for ($i = 2; $i * $i <= $num; $i++) {
        if ($num % $i === 0)
            return false;
    }
    return true;
}

function esCuadradoPerfecto(int $x): bool
{
    if ($x < 0)
        return false;
    $r = (int) round(sqrt($x));
    return ($r * $r === $x);
}

function esFibonacci(int $num): bool
{
    if ($num < 0)
        return false;
    return esCuadradoPerfecto(5 * $num * $num + 4) || esCuadradoPerfecto(5 * $num * $num - 4);
}

// 1) Contar valores primos en posiciones Fibonacci y múltiplo de 3
$p1 = [];
for ($i = 0; $i < $n; $i++) {
    if (esFibonacci($i) && ($i % 3 === 0) && esPrimo($vector[$i])) {
        $p1[] = "Pos[$i]={$vector[$i]}";
    }
}

// 2) Contar valores primos en posiciones primas que sean múltiplos de 3, 4, 5 y 7
$p2Strict = 0;
$p2Detalle = [];
for ($i = 0; $i < $n; $i++) {
    if (esPrimo($i) && esPrimo($vector[$i])) {
        // Condición estricta (múltiplo de 3, 4, 5 y 7 a la vez)
        if ($i % 420 === 0 || $vector[$i] % 420 === 0) {
            $p2Strict++;
        }
        // Condición individual (múltiplo de 3, 4, 5 o 7)
        if (
            $i % 3 === 0 || $i % 4 === 0 || $i % 5 === 0 || $i % 7 === 0 ||
            $vector[$i] % 3 === 0 || $vector[$i] % 4 === 0 || $vector[$i] % 5 === 0 || $vector[$i] % 7 === 0
        ) {
            $p2Detalle[] = "Pos[$i]={$vector[$i]}";
        }
    }
}

// 3) Ordenar con método Burbuja de menor a mayor SOLO primos que aparezcan en Fibonacci
$ordenado = $vector;
$indicesFiboPrimos = [];

for ($i = 0; $i < $n; $i++) {
    if (esPrimo($ordenado[$i]) && esFibonacci($ordenado[$i])) {
        $indicesFiboPrimos[] = $i;
    }
}

$m = count($indicesFiboPrimos);
for ($i = 0; $i < $m - 1; $i++) {
    for ($j = 0; $j < $m - $i - 1; $j++) {
        $posA = $indicesFiboPrimos[$j];
        $posB = $indicesFiboPrimos[$j + 1];
        if ($ordenado[$posA] > $ordenado[$posB]) {
            $aux = $ordenado[$posA];
            $ordenado[$posA] = $ordenado[$posB];
            $ordenado[$posB] = $aux;
        }
    }
}

// 4) Promedio y clasificación N > promedio y N < promedio después de ordenar
$promedio = round(array_sum($ordenado) / $n, 2);
$mayores = [];
$menores = [];

for ($i = 0; $i < $n; $i++) {
    if ($ordenado[$i] > $promedio) {
        $mayores[] = "Pos[$i]={$ordenado[$i]}";
    } elseif ($ordenado[$i] < $promedio) {
        $menores[] = "Pos[$i]={$ordenado[$i]}";
    }
}

// Formatear vectores para vista simple
$strOrig = [];
$strOrd = [];
for ($i = 0; $i < $n; $i++) {
    $strOrig[] = "[$i]:{$vector[$i]}";
    $strOrd[] = "[$i]:{$ordenado[$i]}";
}

echo json_encode([
    'original' => implode(' | ', $strOrig),
    'p1_count' => count($p1),
    'p1_detail' => empty($p1) ? 'Ninguno' : implode(', ', $p1),
    'p2_count' => $p2Strict,
    'p2_detail' => empty($p2Detalle) ? 'Ninguno' : count($p2Detalle) . ' (' . implode(', ', $p2Detalle) . ')',
    'ordenado' => implode(' | ', $strOrd),
    'promedio' => $promedio,
    'mayores' => empty($mayores) ? 'Ninguno' : implode(', ', $mayores),
    'menores' => empty($menores) ? 'Ninguno' : implode(', ', $menores)
]);