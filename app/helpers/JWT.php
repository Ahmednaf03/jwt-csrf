<?php 

function generateToken($id,$email){
$iat = time(); 
$exp = $iat + (60 * 5); // 5 minutes
$secret = $_ENV['JWT_SECRET'];
// payload in associative array
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

//array to json/string
$headerjson = json_encode($header);
$payloadjson = json_encode($payload);

//json/string to base64
$encodedHeader = base64_encode($headerjson);
$encodedPayload = base64_encode($payloadjson);

//signature gen with hmac using secretKey
$signature  = hash_hmac('sha256', "$encodedHeader.$encodedPayload", $secret, false);
$jwt = "$encodedHeader.$encodedPayload.$signature";

return $jwt;

}


// validate function
function validateToken($jwt){
    $secret = $_ENV['JWT_SECRET'];
    // split toke into 3 parts
    $parts = explode('.', $jwt);
    $encodedHeader = $parts[0];
    $encodedPayload = $parts[1];
    $signature = $parts[2];

    // decode payload from base64 into assoc
    $decodedPayload = json_decode(base64_decode($encodedPayload), true);

    // check expiry time
    if ($decodedPayload['exp'] < time()) {
        return false;
    }
    //re calculate signature and compare with signature in token
    $calculatedSignature = hash_hmac('sha256', "$encodedHeader.$encodedPayload", $secret, false);
    if ($signature !== $calculatedSignature) {
        return false;
    }

    return $decodedPayload;
}


?>