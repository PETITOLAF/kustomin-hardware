<?php
require_once __DIR__ . '/../config.php';
header('Content-Type: application/json; charset=utf-8');
$data = json_decode(file_get_contents('php://input'), true);
$name = isset($data['name']) ? $data['name'] : 'Client';
$email = isset($data['email']) ? $data['email'] : '';
$message = isset($data['message']) ? $data['message'] : '';
$to = ORDER_EMAILS;
$subject = 'Message contact Kustomin';
$body = "Message de: ${name} <${email}>\n\n" . $message;
$headers = 'From: '.FROM_EMAIL."\r\n" . 'Reply-To: '.($email?:FROM_EMAIL);
$ok = mail($to, $subject, $body, $headers);
if($ok) echo json_encode(['success'=>true]); else { http_response_code(500); echo json_encode(['error'=>'failed']); }
?>