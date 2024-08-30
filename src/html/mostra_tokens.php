<?php

if (isset($_GET['nome_estrutura_frase'])) {
	$nome_estrutura_frase = $_GET['nome_estrutura_frase'];
} else {
	$nome_estrutura_frase = "entrega no passado";
}

$nome_estrutura_frase = str_replace("_"," ",$nome_estrutura_frase);

function mostra_tokens_no_automata($id_tipo_elemento_sintatico)
{

# VPM 20240505 (ainda nao implementado) -> select testado para pegar o indicador a partir de qualquer token: select id_chave_tipo_elemento_sintatico, nome_frase, nome_tipo_elemento_sintatico as tipo, id_chave_frase from tipos_elementos_sintaticos, frases, tokens_nas_frases, tokens, tipos_tokens where id_tipo_elemento_sintatico="1" and id_tipo_elemento_sintatico =id_chave_tipo_elemento_sintatico and id_chave_frase=id_frase and id_token = id_chave_token and id_tipo_token = id_chave_tipo_token and nome_token like "pub%" group by nome_frase order by id_chave_frase, ordem;
# VPM 20240824 : criado a partir de ppapdi.php para mostrar o automata num inseridencia_frases.php


include "identifica.php.cripto";
include "tabela_propriedades.php";
global $matriz_de_propriedades;
global $conta_frases;
global $nome_estrutura_frase;
file_put_contents("matriz_de_propriedades.txt",json_encode($matriz_de_propriedades));

file_put_contents("query3.txt", "Início\n\n");
file_put_contents("linhas_de_elementos.txt", "Início\n\n");




function deletarBackupsAntigos($diretorio) { // funcao gerada pelo Chat GPT

    // Buscar todos os arquivos do diretório
    $arquivos = glob($diretorio . "*.sql");

    // Filtrar arquivos que correspondem ao padrão de nome com timestamp
    $backups = array_filter($arquivos, function($arquivo) {
        return preg_match('/script_recuperacao_\d{8}-\d{6}\.sql$/', $arquivo);
    });

    // Ordenar os backups pela data de modificação em ordem decrescente
    usort($backups, function($a, $b) {
        return filemtime($b) - filemtime($a);
    });

    // Se há mais de 10 backups, excluir os antigos
    if (count($backups) > 10) {
        for ($i = 10; $i < count($backups); $i++) {
            unlink($backups[$i]);
        }
    }
} // deletarBackupsAntigos

// Uso
$diretorio = "";

deletarBackupsAntigos($diretorio);

$timestampAtual = time();
$stampArquivo = date('Ymd-His', $timestampAtual);

$nome_arquivo_script = "script_recuperacao_".$stampArquivo.".sql";

function cabecalio_script(){
	global $nome_arquivo_script;

	$linhas="
start transaction;
delete from frases;

"; 
	file_put_contents($nome_arquivo_script, $linhas);

}

function pezeira_script(){
	global $nome_arquivo_script;

	$linhas="
commit;
"; 
	file_put_contents($nome_arquivo_script, $linhas, FILE_APPEND);

}



function mostra_frases($id_tipo_sintatico)
{

global $conta_frases;
global $dsn;
global $user;
global $pass;
global $nome_arquivo_script;
$conta_frases=0;
	$pdo4 = new PDO($dsn, $user, $pass);
	$id_frase="" ;
	$linha="";
	$finalizacao="";
	$close_div="";
	$query3 = 'select id_chave_tipo_elemento_sintatico, nome_frase, nome_tipo_elemento_sintatico as tipo, nome_token_na_frase, nome_token, id_chave_frase, ordem, id_chave_tipo_token, nome_tipo_token from tipos_elementos_sintaticos, frases, tokens_nas_frases, tokens, tipos_tokens where id_tipo_elemento_sintatico="'.$id_tipo_sintatico.'" and id_tipo_elemento_sintatico =id_chave_tipo_elemento_sintatico and id_chave_frase=id_frase and id_token = id_chave_token and id_tipo_token = id_chave_tipo_token
	order by id_chave_frase, ordem;';
	$stmt3 = $pdo4->prepare($query3);
	$stmt3->execute();
	$rows3 = $stmt3->fetchAll(PDO::FETCH_ASSOC);
	$nome_frase = "";
	$espaco="";
	file_put_contents("query3.txt", $query3."\n\n", FILE_APPEND);
	file_put_contents("linhas_de_elementos.txt", $query3."> ".$id_tipo_sintatico." ".count($rows3)."\n", FILE_APPEND);
	$id_frase_velho="";
	if (count($rows3)>0) {
	foreach ($rows3 as $row3) {
		$nome_token			= $row3["nome_token"];
		$nome_tipo_token			= $row3["nome_tipo_token"];
		$id_tipo_token		= $row3["id_chave_tipo_token"];
		$id_frase			= $row3["id_chave_frase"];
		$nome_frase_banco   = $row3['nome_frase'];
		$ordem			    = $row3['ordem'];
		$nome_tipo_elem_sin = $row3['tipo'];
		$id_chave_elemento_sintatico = $row3['id_chave_tipo_elemento_sintatico'];
		$nome_frase = $nome_frase.$espaco.$nome_token;

	if (strlen($nome_token)>0) {$espaco=" ";}
		$botao_delete="<br><input id='delete_".$id_frase."' type='button' class='deletar' data-id-frase='".$id_frase."' value='apaga'/>";
		$botao_recicla="<input id='recicla_".$id_frase."' type='button' class='reciclar' data-id-frase='".$id_frase."' data-id-elemento-sintatico='".$id_chave_elemento_sintatico."' data-nome-elemento-sintatico='".str_replace(" ","_",$nome_tipo_elem_sin)."' value='recicla'/>";
	if ($id_frase != $id_frase_velho) {
			$conta_frases++;
			$linha_sql = "\nINSERT INTO frases (nome_frase, id_tipo_elemento_sintatico) VALUES ('".$nome_frase_banco."',".$id_chave_elemento_sintatico.");\n\n";
			//file_put_contents($nome_arquivo_script, $linha_sql, FILE_APPEND);
			if (strlen($nome_token)>0) {$espaco_inicial=" ";} else {$espaco_inicial="";}
			$linha = $linha.$close_div."<div id='frase_".$id_frase."' class='frase'>".$nome_token." ";
		} else {
			$linha = $linha.$espaco.$nome_token;
		}
			$close_div= $botao_delete.$botao_recicla."</div>";

	$linha_sql_token = "INSERT INTO tokens_nas_frases (nome_token_na_frase, id_frase, id_token, ordem) VALUES ('".$nome_token."', (SELECT id_chave_frase FROM frases where nome_frase='".$nome_frase_banco."'), (SELECT id_chave_token FROM tokens WHERE nome_token='".$nome_token."' and id_tipo_token=(SELECT id_chave_tipo_token FROM tipos_tokens WHERE nome_tipo_token = '".$nome_tipo_token."')), ".$ordem.");\n";
	//file_put_contents($nome_arquivo_script, $linha_sql_token, FILE_APPEND);
		$id_frase_velho = $id_frase;
	}
				file_put_contents("linha.txt",$linha, FILE_APPEND);
				file_put_contents("linhas_de_elementos.txt", $linha, FILE_APPEND);
				$finalizacao=$botao_delete.$botao_recicla."</div>";
	}
	return $linha.$finalizacao;
} // fim function mostra_frases


$host = 'localhost';
$db   = $nome_base_dados;
$user = $username;;
$charset = 'utf8mb4';


$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$pdo = new PDO($dsn, $user, $pass);
$pdo2 = new PDO($dsn, $user, $pass);


echo '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerindica</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>';

echo '<div id="tempHeader"><div id="titulo_geral">Gerindica<br>da<br>pePÁPIDI</div><img id="logo_funda" src="logo_fundacentro.jpeg"></img></div>';



//cabecalio_script();

$array_categorias = array(); // esta matriz guarda ordenadamente a sequências de id_categorias (identifiadores dos elementos da arvore) que efetivamente guardam tokens (descarta elementos das arvores que contem outros elementos e pega apenas as folhas, guardando o id_categoria da tabela secoes)
unset($array_categorias);

$query = 'call mostra_documento_completo_niveis_sem_lixeira_automata("estrutura");';
$stmt = $pdo->prepare($query);
$stmt->execute();

$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

$fecha_div="";
$frases="";
$separador_cria ="<div class='separator'>Crie um indicador</div>"; 
$separador = "";
$linha = 0;
$ordem=0;
$id_tipo_elemento_sintatico="0";
$nome_classe_tipo_sintatico = "";
$flag=0; // nao permite que os filhos sejam tratados
foreach ($rows as $row) {

$nivel 				= $row["niveis_temp"];
$nome_tipo_secao 		= $row["nome_tipo_secao"];
$trecho				= $row["trecho"];
$nome_tipo_token 	= $row["nome_tipo_token"];
$exp_sql		 	= $row["exp_sql"]; // pode ser o nome de uma estrutura ou um sql de busca
$id_secao 			= $row["id_secao"]; // tambem conhecido como id_chave_categoria na tabela de secoes

if ($nome_tipo_secao == "estrutura") {
	$flag=0; // nao permite que os filhos sejam tratados
}

if ($exp_sql == $nome_estrutura_frase || ($nome_tipo_secao != "estrutura" && $flag==1)) {

if ($nome_tipo_secao == "estrutura" && $nivel == 1)
		{
			$flag=1; // permite que os filhos de $exp_sql sejam tratados
			$query2 ="select id_chave_tipo_elemento_sintatico from tipos_elementos_sintaticos where nome_tipo_elemento_sintatico='".$exp_sql."';";
			$stmt2 = $pdo2->prepare($query2);
			$stmt2->execute();
			$rows2 = $stmt2->fetchAll(PDO::FETCH_ASSOC);
			foreach ($rows2 as $row2) {
				$id_tipo_elemento_sintatico = $row2["id_chave_tipo_elemento_sintatico"];
				$nome_classe_tipo_sintatico = $exp_sql;
			}

//			if (strlen($fecha_div)>0) {
			// echo "<br>";
				$frases = "";// mostra_frases($id_tipo_elemento_sintatico);
				//echo "<script>setTimeout(function () {document.getElementById('total_indicadores_".$id_tipo_elemento_sintatico."').innerText='".$conta_frases."';},1000)</script>";
//			}

$separador = ""; //"<div class='separator'>Indicadores criados (Total: <span id='total_indicadores_".$id_tipo_elemento_sintatico."'></span> )</div>";
			
			echo $fecha_div.'<div id="tipo_sintatico_'.str_replace(" ","_",$exp_sql).'" class="tipo_automata" data-id-tipo-elemento-sintatico="'.$id_tipo_elemento_sintatico.'"><div class="titulo_tipo_elemento_sintatico"><b>'.$exp_sql." (".$id_tipo_elemento_sintatico.")".'</b></div>';
			echo "<div class='contem_botao'><input class='botao' id='botao_".$id_tipo_elemento_sintatico."' style='float: right' type='button' disabled  value='guarda' data-id-tipo-sintatico='".$id_tipo_elemento_sintatico."' data-nome-tipo-sintatico='".str_replace(" ", "_",$nome_classe_tipo_sintatico)."'></div>";
			echo '<br>'; //.$separador_cria;	
			$ordem=0;
		}
else 
		{
			$cor_de_fundo = "white";
			$cor_da_fonte = "black";
		  	if (isset($matriz_de_propriedades[$nome_tipo_token])){	
					if (isset($matriz_de_propriedades[$nome_tipo_token])){
							$cor_de_fundo = $matriz_de_propriedades[$nome_tipo_token]["cor_de_fundo"];
							$cor_da_fonte = $matriz_de_propriedades[$nome_tipo_token]["cor_da_fonte"];
							if ($cor_da_fonte == "cor_original") {$cor_da_fonte = "gray";}
					}
			}
			echo'
			<div class="automata" style="background-color: '.$cor_de_fundo.'; color:'.$cor_da_fonte.'"><div class="titulo_token" style="border: 3px solid '.$cor_da_fonte.'; color: '.$cor_da_fonte.';">'.$nome_tipo_token.'</div><div class="puxa_direita"><span id="marca_'.$linha.'" class="marca marca_class_'.$id_secao.'" data-selecionou="nao">&#10004;</span></div><br>
				<div id="drop_'.$linha.'" class="dropdown-wrapper" data-sql="'.$exp_sql.'"  style="background-color: '.$cor_de_fundo.'; color:'.$cor_da_fonte.'" >
				    <input id="input_'.$linha.'" data-companion="marca_'.$linha.'" type="text" class="search-input '.str_replace(" ","_",$nome_classe_tipo_sintatico).' input_class_'.$id_secao.'" data-nome-tipo-sintatico="'.str_replace(" ","_",$nome_classe_tipo_sintatico).'" placeholder="Digite para buscar..." data-id-token="" data-ordem="'.$ordem.'" data-id-tipo-elemento-sintatico="'.$id_tipo_elemento_sintatico.'" data-id-categoria="'.$id_secao.'">
				    <div class="results" tabindex="-1" style="background-color: '.$cor_de_fundo.'; color: '.$cor_da_fonte.'"></div>
				</div>
			</div>
			';
			$array_categorias[$id_tipo_elemento_sintatico][$ordem] = $id_secao;

			//if ($ordem == 0) {file_put_contents($nome_arquivo_script, "\n\n# corrigindo o id_categoria da tabela frases \n\n", FILE_APPEND);}

			//file_put_contents($nome_arquivo_script, "UPDATE tokens_nas_frases SET id_categoria =".$id_secao." WHERE ordem= ".$ordem." AND id_frase IN (SELECT id_chave_frase from frases where id_tipo_elemento_sintatico = ".$id_tipo_elemento_sintatico."); \n "  , FILE_APPEND);

		     $linha++;
			 $ordem++;
		}

$fecha_div=$separador.$frases."<br><br></div>";
} // fim if entrega no passado

}

//file_put_contents("matriz_de_id_secao.txt",json_encode($array_categorias));

//mostra_frases($id_tipo_elemento_sintatico);
				
echo $fecha_div;
echo '<div id="resultado"></div>';
echo '
    <script src="script.js"></script> 
    
<script>

function enviarDadosParaMostraFrases(nomeClasseInputs, idTipoSintatico) {
    // Coletar todos os inputs com a classe especificada

    console.log(nomeClasseInputs);
    console.log(idTipoSintatico);
    let inputas = document.querySelectorAll("." + nomeClasseInputs);
    var searchStrings = [];
    console.log("Inputs coletados:");
    console.log(inputas); // Para debug
    // Iterar sobre os inputs e coletar seus valores
    inputas.forEach(function(input) {
        searchStrings.push(input.value);
    });

    // Preparar os dados para envio via POST
    var formData = new FormData();
    formData.append("id_tipo_sintatico", idTipoSintatico);

    // Adicionar cada searchString ao FormData
    searchStrings.forEach(function(value, index) {
        formData.append("searchStrings[]", value);
    });
    console.log("Enviando dados para mostra_frases.php:"); 
    console.log(searchStrings); // Para debug
    
    // Fazer a requisição POST ao arquivo mostra_frases.php
    fetch("mostra_frases.php", {
        method: "POST",
        body: formData
    })
    .then(response => response.text())
    .then(data => {
        // Manipular a resposta, por exemplo, exibir na página
        console.log("Resposta do servidor:");
        console.log(data); // Para debug
        document.getElementById("resultado").innerHTML = data;
    })
    .catch(error => {
        console.error("Erro:", error);
    });
}



    const searchInputas = document.querySelectorAll(".search-input");
    console.log(searchInputas);
    searchInputas.forEach(input => {
	input.addEventListener("keydown", function() {
		let este=this;
		setTimeout( function () {
		if (document.getElementById(input.getAttribute("data-companion")).getAttribute("data-selecionou") == "sim" || este.value.length == 0) {
			enviarDadosParaMostraFrases("search-input", input.getAttribute("data-id-tipo-elemento-sintatico"));	
		} 
		}, 200);
		
	
	});
    });
</script>
</body>
</html>';
//pezeira_script();
} // fim function mostra_tokens_no_automata

mostra_tokens_no_automata(1);


?>

