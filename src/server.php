<?php
header('Content-Type: application/json');

$response = [
  'code' => '200',
  'success' => 'Welcome to Arnelify POD framework.'
];

echo json_encode($response);