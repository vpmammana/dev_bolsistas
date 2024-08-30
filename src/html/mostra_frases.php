<?php
// Inclua a configuração ou conexão com o banco de dados, se necessário
include "identifica.php.cripto";

$user = $username;
$dsn="mysql:host=localhost;dbname=$nome_base_dados;charset=utf8";

function mostra_frases($id_tipo_sintatico, $searchStrings = [])
{
    global $dsn, $user, $pass;

    $conta_frases = 0;
    $pdo4 = new PDO($dsn, $user, $pass);
    $id_frase = "";
    $linha = "";
    $finalizacao = "";
    $close_div = "";

    // Início da query base
    $query3 = 'SELECT id_chave_tipo_elemento_sintatico, nome_frase, nome_tipo_elemento_sintatico AS tipo, 
               nome_token_na_frase, nome_token, id_chave_frase, ordem, id_chave_tipo_token, nome_tipo_token 
               FROM tipos_elementos_sintaticos, frases, tokens_nas_frases, tokens, tipos_tokens 
               WHERE id_tipo_elemento_sintatico = :id_tipo_sintatico 
               AND id_tipo_elemento_sintatico = id_chave_tipo_elemento_sintatico 
               AND id_chave_frase = id_frase 
               AND id_token = id_chave_token 
               AND id_tipo_token = id_chave_tipo_token';

    // Adicionar condições de busca com LIKE, se houver strings de busca
    $conditions = [];
    $params = ['id_tipo_sintatico' => $id_tipo_sintatico];

    foreach ($searchStrings as $index => $string) {
        if (!empty($string)) {
            $conditions[] = "nome_frase LIKE :string$index";
            $params["string$index"] = "%$string%";
        }
    }

    // Se houver condições de busca, adicioná-las à query
    if (count($conditions) > 0) {
        $query3 .= ' AND (' . implode(' AND ', $conditions) . ')';
    } else {
        // Se todas as strings forem vazias, retorne sem executar a consulta
        return '';
    }

    $query3 .= ' ORDER BY id_chave_frase, ordem';

    $stmt3 = $pdo4->prepare($query3);
    $stmt3->execute($params);
    $rows3 = $stmt3->fetchAll(PDO::FETCH_ASSOC);

    $nome_frase = "";
    $espaco = "";
    $id_frase_velho = "";

    if (count($rows3) > 0) {
        foreach ($rows3 as $row3) {
            $nome_token = $row3["nome_token"];
            $nome_tipo_token = $row3["nome_tipo_token"];
            $id_tipo_token = $row3["id_chave_tipo_token"];
            $id_frase = $row3["id_chave_frase"];
            $nome_frase_banco = $row3['nome_frase'];
            $ordem = $row3['ordem'];
            $nome_tipo_elem_sin = $row3['tipo'];
            $id_chave_elemento_sintatico = $row3['id_chave_tipo_elemento_sintatico'];
            $nome_frase = $nome_frase . $espaco . $nome_token;

            if (strlen($nome_token) > 0) {
                $espaco = " ";
            }
            $botao_delete = "<br><input id='delete_" . $id_frase . "' type='button' class='deletar' data-id-frase='" . $id_frase . "' value='apaga'/>";
            $botao_recicla = "<input id='recicla_" . $id_frase . "' type='button' class='reciclar' data-id-frase='" . $id_frase . "' data-id-elemento-sintatico='" . $id_chave_elemento_sintatico . "' data-nome-elemento-sintatico='" . str_replace(" ", "_", $nome_tipo_elem_sin) . "' value='recicla'/>";

            if ($id_frase != $id_frase_velho) {
                $conta_frases++;
                $linha_sql = "\nINSERT INTO frases (nome_frase, id_tipo_elemento_sintatico) VALUES (:nome_frase_banco, :id_chave_elemento_sintatico);\n\n";
                $stmt_insert = $pdo4->prepare($linha_sql);
                $stmt_insert->execute([
                    'nome_frase_banco' => $nome_frase_banco,
                    'id_chave_elemento_sintatico' => $id_chave_elemento_sintatico
                ]);

                if (strlen($nome_token) > 0) {
                    $espaco_inicial = " ";
                } else {
                    $espaco_inicial = "";
                }
                $linha = $linha . $close_div . "<div id='frase_" . $id_frase . "' class='frase'>" . $nome_token . " ";
            } else {
                $linha = $linha . $espaco . $nome_token;
            }
            $close_div = $botao_delete . $botao_recicla . "</div>";

            $linha_sql_token = "INSERT INTO tokens_nas_frases (nome_token_na_frase, id_frase, id_token, ordem) VALUES (:nome_token, (SELECT id_chave_frase FROM frases WHERE nome_frase=:nome_frase_banco), (SELECT id_chave_token FROM tokens WHERE nome_token=:nome_token AND id_tipo_token=(SELECT id_chave_tipo_token FROM tipos_tokens WHERE nome_tipo_token = :nome_tipo_token)), :ordem);\n";
            $stmt_insert_token = $pdo4->prepare($linha_sql_token);
            $stmt_insert_token->execute([
                'nome_token' => $nome_token,
                'nome_frase_banco' => $nome_frase_banco,
                'nome_tipo_token' => $nome_tipo_token,
                'ordem' => $ordem
            ]);

            $id_frase_velho = $id_frase;
        }

        $finalizacao = $botao_delete . $botao_recicla . "</div>";
    }
    return $linha . $finalizacao;
}

// Recebendo os dados via POST
$id_tipo_sintatico = $_POST['id_tipo_sintatico'] ?? null;
$searchStrings = $_POST['searchStrings'] ?? [];

// Validando e sanitizando os inputs
if ($id_tipo_sintatico !== null && is_array($searchStrings)) {
    // Exibe os resultados da função mostra_frases
    echo mostra_frases($id_tipo_sintatico, $searchStrings);
} else {
    echo "Parâmetros inválidos.";
}
?>

