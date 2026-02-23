<?php
declare(strict_types=1);
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: https://free-bitcoin.lat');
header('Cache-Control: public, max-age=86400');

// Fee tables — update quarterly. Source of truth.
$FEE_TABLES = [
    'us-mx' => [
        'corridor' => 'us-mx',
        'from' => 'Estados Unidos',
        'to' => 'México',
        'currency' => 'MXN',
        'updatedAt' => '2026-02-23',
        'options' => [
            ['method'=>'Bitcoin Lightning','provider'=>'Strike / Muun','feeUSD'=>0.01,'fxSpreadPct'=>0.1,'totalCostUSD'=>0.5,'speed'=>'< 1 minuto','risk'=>'bajo','notes'=>'Strike disponible en México. Destinatario recibe MXN via SPEI.'],
            ['method'=>'USDT P2P','provider'=>'Binance P2P','feeUSD'=>0,'fxSpreadPct'=>1.5,'totalCostUSD'=>1.5,'speed'=>'10–30 min','risk'=>'medio','notes'=>'Usar vendedores verificados con >100 trades y >98% satisfacción.'],
            ['method'=>'Airtm','provider'=>'Airtm','feeUSD'=>2.0,'fxSpreadPct'=>1.0,'totalCostUSD'=>3.0,'speed'=>'1–24 h','risk'=>'bajo','notes'=>'Acepta OXXO, SPEI, Mercado Pago. Buena para sin cuenta bancaria.'],
            ['method'=>'Western Union / MoneyGram','provider'=>'WU / MG','feeUSD'=>5.0,'fxSpreadPct'=>3.0,'totalCostUSD'=>8.0,'speed'=>'Minutos–1 día','risk'=>'muy bajo','notes'=>'Red física amplia. Alto costo en envíos pequeños.'],
            ['method'=>'Banco (ACH/SPEI)','provider'=>'Bancos tradicionales','feeUSD'=>25.0,'fxSpreadPct'=>2.5,'totalCostUSD'=>27.5,'speed'=>'1–3 días hábiles','risk'=>'muy bajo','notes'=>'El método más caro. Requiere cuenta bancaria en ambos lados.'],
        ],
    ],
    'us-gt' => [
        'corridor'=>'us-gt','from'=>'Estados Unidos','to'=>'Guatemala','currency'=>'GTQ','updatedAt'=>'2026-02-23',
        'options'=>[
            ['method'=>'Bitcoin Lightning','provider'=>'Strike','feeUSD'=>0.01,'fxSpreadPct'=>0.1,'totalCostUSD'=>0.5,'speed'=>'< 1 min','risk'=>'bajo','notes'=>'Strike disponible en Guatemala. Remesas = ~19% del PIB.'],
            ['method'=>'USDT P2P','provider'=>'Binance P2P','feeUSD'=>0,'fxSpreadPct'=>2.0,'totalCostUSD'=>2.0,'speed'=>'15–60 min','risk'=>'medio','notes'=>'Alta adopción cripto en Guatemala.'],
            ['method'=>'Airtm','provider'=>'Airtm','feeUSD'=>2.5,'fxSpreadPct'=>1.5,'totalCostUSD'=>4.0,'speed'=>'1–24 h','risk'=>'bajo','notes'=>'Disponible en Guatemala con opciones de efectivo.'],
            ['method'=>'Western Union','provider'=>'Western Union','feeUSD'=>5.0,'fxSpreadPct'=>3.5,'totalCostUSD'=>8.5,'speed'=>'Minutos','risk'=>'muy bajo','notes'=>'Red amplia. Las remesas son ~19% del PIB guatemalteco.'],
        ],
    ],
    'us-hn' => [
        'corridor'=>'us-hn','from'=>'Estados Unidos','to'=>'Honduras','currency'=>'HNL','updatedAt'=>'2026-02-23',
        'options'=>[
            ['method'=>'Bitcoin Lightning','provider'=>'Strike','feeUSD'=>0.01,'fxSpreadPct'=>0.1,'totalCostUSD'=>0.5,'speed'=>'< 1 min','risk'=>'bajo','notes'=>'Alta adopción Lightning en Honduras. Remesas = ~20% del PIB.'],
            ['method'=>'USDT P2P','provider'=>'Binance P2P','feeUSD'=>0,'fxSpreadPct'=>2.0,'totalCostUSD'=>2.0,'speed'=>'15–60 min','risk'=>'medio','notes'=>'Buena liquidez en corredor US-HN.'],
            ['method'=>'Airtm','provider'=>'Airtm','feeUSD'=>2.5,'fxSpreadPct'=>1.5,'totalCostUSD'=>4.0,'speed'=>'1–24 h','risk'=>'bajo','notes'=>'Disponible en Honduras.'],
            ['method'=>'Western Union / Ria','provider'=>'WU / Ria','feeUSD'=>4.0,'fxSpreadPct'=>3.0,'totalCostUSD'=>7.0,'speed'=>'Minutos','risk'=>'muy bajo','notes'=>'Red física amplia.'],
        ],
    ],
    'us-sv' => [
        'corridor'=>'us-sv','from'=>'Estados Unidos','to'=>'El Salvador','currency'=>'USD','updatedAt'=>'2026-02-23',
        'options'=>[
            ['method'=>'Bitcoin Lightning (Chivo/Strike)','provider'=>'Chivo Wallet / Strike','feeUSD'=>0,'fxSpreadPct'=>0,'totalCostUSD'=>0,'speed'=>'Segundos','risk'=>'bajo','notes'=>'Sin conversión de divisa. El Salvador usa USD. Aceptación ahora voluntaria (Ley enmendada 2025).'],
            ['method'=>'USDT P2P','provider'=>'Binance P2P','feeUSD'=>0,'fxSpreadPct'=>0.5,'totalCostUSD'=>0.5,'speed'=>'15–30 min','risk'=>'bajo','notes'=>'Sin riesgo cambiario (USD/USD). Remesas = ~24% del PIB.'],
            ['method'=>'Airtm','provider'=>'Airtm','feeUSD'=>1.5,'fxSpreadPct'=>0.5,'totalCostUSD'=>2.0,'speed'=>'1–4 h','risk'=>'muy bajo','notes'=>'Disponible en El Salvador.'],
            ['method'=>'Western Union','provider'=>'Western Union','feeUSD'=>4.0,'fxSpreadPct'=>0,'totalCostUSD'=>4.0,'speed'=>'Minutos','risk'=>'muy bajo','notes'=>'Red física amplia.'],
        ],
    ],
    'us-co' => [
        'corridor'=>'us-co','from'=>'Estados Unidos','to'=>'Colombia','currency'=>'COP','updatedAt'=>'2026-02-23',
        'options'=>[
            ['method'=>'Bitcoin Lightning','provider'=>'Strike / Muun','feeUSD'=>0.01,'fxSpreadPct'=>0.2,'totalCostUSD'=>0.6,'speed'=>'< 1 min','risk'=>'bajo','notes'=>'Alta adopción cripto en Colombia.'],
            ['method'=>'USDT P2P','provider'=>'Binance P2P','feeUSD'=>0,'fxSpreadPct'=>1.5,'totalCostUSD'=>1.5,'speed'=>'15–45 min','risk'=>'medio','notes'=>'Nequi y Bancolombia son métodos populares de pago P2P.'],
            ['method'=>'Western Union / Remitly','provider'=>'WU / Remitly','feeUSD'=>4.0,'fxSpreadPct'=>2.5,'totalCostUSD'=>6.5,'speed'=>'Minutos–1 día','risk'=>'muy bajo','notes'=>'Remesas = ~2.8% del PIB colombiano.'],
        ],
    ],
    'us-pe' => [
        'corridor'=>'us-pe','from'=>'Estados Unidos','to'=>'Perú','currency'=>'PEN','updatedAt'=>'2026-02-23',
        'options'=>[
            ['method'=>'Bitcoin Lightning','provider'=>'Strike','feeUSD'=>0.01,'fxSpreadPct'=>0.2,'totalCostUSD'=>0.6,'speed'=>'< 1 min','risk'=>'bajo','notes'=>'Adopción Lightning en crecimiento en Perú.'],
            ['method'=>'USDT P2P','provider'=>'Binance P2P','feeUSD'=>0,'fxSpreadPct'=>1.8,'totalCostUSD'=>1.8,'speed'=>'20–60 min','risk'=>'medio','notes'=>'Yape y Plin son métodos de retiro populares en P2P peruano.'],
            ['method'=>'Western Union / Remitly','provider'=>'WU / Remitly','feeUSD'=>4.5,'fxSpreadPct'=>2.5,'totalCostUSD'=>7.0,'speed'=>'Minutos–1 día','risk'=>'muy bajo','notes'=>'Red amplia en Perú.'],
        ],
    ],
    'us-bo' => [
        'corridor'=>'us-bo','from'=>'Estados Unidos','to'=>'Bolivia','currency'=>'BOB','updatedAt'=>'2026-02-23',
        'options'=>[
            ['method'=>'USDT P2P','provider'=>'Binance P2P','feeUSD'=>0,'fxSpreadPct'=>2.5,'totalCostUSD'=>2.5,'speed'=>'30–90 min','risk'=>'medio','notes'=>'Ban cripto levantado en 2024 (RD 082). Adopción rápida por escasez de USD.'],
            ['method'=>'Bitcoin Lightning','provider'=>'Strike / Muun','feeUSD'=>0.01,'fxSpreadPct'=>0.5,'totalCostUSD'=>0.8,'speed'=>'< 1 min','risk'=>'bajo','notes'=>'Requiere que el destinatario tenga wallet Lightning y P2P para convertir a BOB.'],
            ['method'=>'Western Union / MoneyGram','provider'=>'WU / MG','feeUSD'=>5.0,'fxSpreadPct'=>3.0,'totalCostUSD'=>8.0,'speed'=>'Minutos','risk'=>'muy bajo','notes'=>'Opción más segura para usuarios sin cripto.'],
        ],
    ],
];

$corridor = strtolower($_GET['corridor'] ?? '');
$amountUSD = (float)($_GET['amount'] ?? 200);
$amountUSD = max(1, min(50000, $amountUSD));

if (isset($FEE_TABLES[$corridor])) {
    $data = $FEE_TABLES[$corridor];
    $data['amountUSD'] = $amountUSD;
    // Annotate each option with net amount received estimate
    foreach ($data['options'] as &$opt) {
        $netUSD = $amountUSD - $opt['totalCostUSD'];
        $opt['netUSD'] = max(0, round($netUSD, 2));
        $opt['totalCostPct'] = round(($opt['totalCostUSD'] / $amountUSD) * 100, 2);
    }
    unset($opt);
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
} elseif ($corridor === '') {
    // List available corridors
    echo json_encode([
        'corridors' => array_keys($FEE_TABLES),
        'usage' => '/api/remittance.php?corridor=us-mx&amount=200',
    ]);
} else {
    http_response_code(404);
    echo json_encode(['error' => 'Corridor not found', 'available' => array_keys($FEE_TABLES)]);
}
