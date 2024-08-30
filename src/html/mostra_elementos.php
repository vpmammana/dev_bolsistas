<?php
include "identifica.php.cripto";

$host = 'localhost';
$db   = $nome_base_dados;
$user = $username;
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$pdo = new PDO($dsn, $user, $pass);

$query = 'call mostra_documento_completo_niveis_sem_lixeira_automata("estrutura");';
$stmt = $pdo->prepare($query);
$stmt->execute();

$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Elementos Sintáticos</title>
    <style>
        body {
            background-color: #1c1c1c;
            color: #f0f0f0;
            font-family: Arial, sans-serif;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            margin: 0;
        }
        #tempHeader {
            text-align: center;
            margin-bottom: 20px;
        }
        .item {
            background-color: #333;
            border-radius: 10px;
            padding: 20px;
            margin: 10px;
            width: 80%;
            text-align: center;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        .item:hover {
            background-color: #444;
        }
        .separator {
            color: #ccc;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div id="tempHeader">
        <div id="titulo_geral">Elementos Sintáticos</div>
        <img id="logo_funda" src="logo_fundacentro.jpeg" alt="Logo">
    </div>';

foreach ($rows as $row) {
    $exp_sql = $row["exp_sql"];
    $nivel = $row["niveis_temp"];
    if ($nivel !=1) {continue;}
    echo "<div class='item' onclick=\"window.open('https://hpo3yjcd.specchio.info/dev_vitor/papedins/src/html/mostra_tokens.php?nome_estrutura_frase={$exp_sql}', '_blank')\">
            {$row['exp_sql']}
          </div>";
}


echo '</body></html>';
?>

