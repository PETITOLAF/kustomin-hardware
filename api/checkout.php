<?php
require_once __DIR__ . '/../config.php';
header('Content-Type: application/json; charset=utf-8');
$data = json_decode(file_get_contents('php://input'), true);
$components = isset($data['components']) ? $data['components'] : [];
$customerEmail = isset($data['customerEmail']) ? $data['customerEmail'] : '';
$prices = json_decode(file_get_contents(__DIR__ . '/../data/prices.json'), true);
$sum = 0.0;
foreach($components as $c){ if($c === '' || $c === null) continue; if(!isset($prices['components'][$c])) { http_response_code(400); echo json_encode(['error'=>'Composant inconnu: '.$c]); exit; } $sum += floatval($prices['components'][$c]); }
$total = round($sum * 1.15, 2);
if(defined('STRIPE_SECRET_KEY') && STRIPE_SECRET_KEY !== '' && STRIPE_SECRET_KEY !== 'sk_test_replace_me'){
  $post = [];
  $post[] = 'payment_method_types[]=card';
  $post[] = 'mode=payment';
  $post[] = 'line_items[0][price_data][currency]=eur';
  $post[] = 'line_items[0][price_data][product_data][name]=' . urlencode('Configuration Kustomin');
  $post[] = 'line_items[0][price_data][unit_amount]=' . intval(round($total * 100));
  $post[] = 'line_items[0][quantity]=1';
  $post[] = 'success_url=' . urlencode(BASE_URL . '/public/?success=1');
  $post[] = 'cancel_url=' . urlencode(BASE_URL . '/public/?canceled=1');
  $payload = implode('&', $post);
  $ch = curl_init('https://api.stripe.com/v1/checkout/sessions');
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($ch, CURLOPT_POST, true);
  curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
  curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . STRIPE_SECRET_KEY]);
  $result = curl_exec($ch);
  $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
  if($result === false){ http_response_code(500); echo json_encode(['error'=>'Stripe request failed']); exit; }
  $json = json_decode($result, true);
  if($httpcode >= 200 && $httpcode < 300 && isset($json['url'])) {
    send_order_email($components, $total, $customerEmail);
    echo json_encode(['url' => $json['url']]);
    exit;
  } else {
    http_response_code(500);
    echo json_encode(['error'=>'Stripe error','detail'=>$json]);
    exit;
  }
} else {
  $sent = send_order_email($components, $total, $customerEmail);
  if($sent) {
    echo json_encode(['success' => true, 'message' => 'Commande envoyée par email (pas de paiement configuré).', 'total'=>$total]);
  } else {
    http_response_code(500);
    echo json_encode(['error' => 'Impossible d\'envoyer l\'email.']);
  }
  exit;
}
function send_order_email($components, $total, $customerEmail){
  $to = ORDER_EMAILS;
  $subject = 'Nouvelle commande Kustomin';
  $body = "Nouvelle commande\nTotal: ${total} €\nComposants: " . implode(', ', $components) . "\nEmail client: ${customerEmail}\n\n--\nKustomin Hardware";
  $headers = 'From: '.FROM_EMAIL."\r\n" . 'Reply-To: '.($customerEmail?:FROM_EMAIL) . "\r\n" . 'X-Mailer: PHP/' . phpversion();
  return mail($to, $subject, $body, $headers);
}
?>