<?php 
function generateToken($id,$email){
$iat = time(); 
$exp = $iat + (60 * 60 * 24); // 24 hours in seconds
$secret = $_ENV['JWT_SECRET'];
$payload = [
    "user_id" => $id,
    "email" => $email,
    "iat" => $iat,
    "exp" => $exp
    ];
$header = [
    "alg" => "HS256",
    "typ" => "JWT"
    ];

$headerjson = json_encode($header);
$payloadjson = json_encode($payload);
$encodedHeader = base64_encode($headerjson);
$encodedPayload = base64_encode($payloadjson);
$signature  = hash_hmac('sha256', "$encodedHeader.$encodedPayload", $secret, false);
$jwt = "$encodedHeader.$encodedPayload.$signature";

return $jwt;

}

function validateToken($jwt){
    $secret = $_ENV['JWT_SECRET'];
    $parts = explode('.', $jwt);
    $encodedHeader = $parts[0];
    $encodedPayload = $parts[1];
    $signature = $parts[2];
    $decodedPayload = json_decode(base64_decode($encodedPayload), true);
    if ($decodedPayload['exp'] < time()) {
        return false;
    }
    $calculatedSignature = hash_hmac('sha256', "$encodedHeader.$encodedPayload", $secret, false);
    if ($signature !== $calculatedSignature) {
        return false;
    }
    return $decodedPayload;
}


?>