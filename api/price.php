<?php
header('Content-Type: application/json; charset=utf-8');
$data = json_decode(file_get_contents('php://input'), true);
$components = isset($data['components']) ? $data['components'] : [];
$prices = json_decode(file_get_contents(__DIR__ . '/../data/prices.json'), true);
try {
  $sum = 0.0;
  foreach($components as $c){
    if($c === '' || $c === null) continue;
    if(!isset($prices['components'][$c])) throw new Exception('Composant inconnu: ' . $c);
    $sum += floatval($prices['components'][$c]);
  }
  $total = round($sum * 1.15, 2);
  echo json_encode(['total' => $total]);
} catch(Exception $e){
  http_response_code(400);
  echo json_encode(['error' => $e->getMessage()]);
}
?>