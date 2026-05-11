<?php

$client_id = "PakE6zqpPGuNjoHbppzrOGAF4TkvLTEh89II76XptvXbTEMi";
$client_secret = "uWNGaSMsAOqG7Ci2A2jKBZmdgV8Fng1BbpGGiAbEtGKuyGuaKVGv4X5qwNYy4qaT";
$nik = "9271060312000001";

// ======================
// GENERATE TOKEN
// ======================

$url = "https://api-satusehat-stg.dto.kemkes.go.id/oauth2/v1/accesstoken?grant_type=client_credentials";

$data = http_build_query([
    'client_id' => $client_id,
    'client_secret' => $client_secret
]);

$ch = curl_init();

curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/x-www-form-urlencoded'
]);

$response = curl_exec($ch);

curl_close($ch);

$result = json_decode($response, true);

$token = $result['access_token'] ?? null;


// ======================
// GET PATIENT
// ======================

$api_url = "https://api-satusehat-stg.dto.kemkes.go.id/fhir-r4/v1/Patient?identifier=https://fhir.kemkes.go.id/id/nik|$nik";

$ch = curl_init();

curl_setopt($ch, CURLOPT_URL, $api_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Authorization: Bearer $token",
    "Content-Type: application/json"
]);

$api_response = curl_exec($ch);

curl_close($ch);

$patient = json_decode($api_response, true);

?>

<!DOCTYPE html>
<html>
<head>
    <title>SATUSEHAT API TEST</title>

    <style>
        body{
            font-family: Arial;
            background:#f4f4f4;
            padding:40px;
        }

        .card{
            background:white;
            padding:20px;
            border-radius:10px;
            margin-bottom:20px;
            box-shadow:0 0 10px rgba(0,0,0,0.1);
        }

        h2{
            margin-top:0;
        }

        pre{
            background:#eee;
            padding:15px;
            overflow:auto;
            border-radius:8px;
        }

        .success{
            color:green;
            font-weight:bold;
        }

        .error{
            color:red;
            font-weight:bold;
        }
    </style>
</head>
<body>

<div class="card">
    <h2>Generate Token</h2>

    <?php if($token): ?>
        <p class="success">Token berhasil dibuat</p>

        <b>Access Token:</b>

        <pre><?= $token ?></pre>

    <?php else: ?>

        <p class="error">Gagal generate token</p>

        <pre><?php print_r($result); ?></pre>

    <?php endif; ?>
</div>

<div class="card">
    <h2>Response Patient</h2>

    <?php

    if(isset($patient['entry'])){

        echo "
        <table border='1' cellpadding='10' cellspacing='0'>
            <tr>
                <th>No</th>
                <th>IHS Number</th>
                <th>Nama</th>
                <th>NIK</th>
            </tr>
        ";

        $no = 1;

        foreach($patient['entry'] as $p){

            $resource = $p['resource'];

            $id = $resource['id'] ?? '-';

            $nama = $resource['name'][0]['text'] ?? '-';

            $nik = '-';

            if(isset($resource['identifier'])){

                foreach($resource['identifier'] as $identifier){

                    if($identifier['system'] == 'https://fhir.kemkes.go.id/id/nik'){

                        $nik = $identifier['value'];

                    }

                }

            }

            echo "
            <tr>
                <td>$no</td>
                <td>$id</td>
                <td>$nama</td>
                <td>$nik</td>
            </tr>
            ";

            $no++;

        }

        echo "</table>";

    }else{

        echo "Data tidak ditemukan";

    }
    ?>
</div>

</body>
</html>