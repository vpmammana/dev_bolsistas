<?php

unset($matriz_de_propriedades);

function termina_de_completar_tabela_propriedades($nome_tipo_secao, $username, $pass, $database){ // a tabela nao estava completa porque foi preenchida apenas com os elementos da arvore principal e nao com todos os possiveis elementos da arvore de tipos de elementos

global $matriz_de_propriedades;

	$conn3= new mysqli("localhost", $username, $pass, $database);
	$sql3="call retorna_valores_de_propriedades_do_tipo_secao('".$nome_tipo_secao."');";
//	file_put_contents("debug2_propriedades_sql3.txt", $sql3."\n\n", FILE_APPEND);
	$result3=$conn3->query("$sql3");
	$style="";
	$redutor_de_largura_de_div = 1;
		if ($result3->num_rows>0){
		$pula = 0;  // pula
		$pula_cor = 0; // pula_cor
		$pula_cor_fonte = 0; // pula_cor_fonte
		$rotulo[$nome_tipo_secao]="nao"; // rotulo
		$cores[$nome_tipo_secao]="cor_original";  // cores
		$cor_da_fonte[$nome_tipo_secao]="black"; // cor_da_fonte
        $ifnull = "";
		$titulo_ifnull=""; 
		while ($row3=$result3->fetch_assoc()){
			$propriedade = $row3["nome_propriedade"];
			$valor       = $row3["nome_valor_discreto"];
			$tipo	     = $row3["nome_nested_tipo_secao"];
			$descricao3  = $row3["descricao"];
			if (isset($matriz_de_propriedades[$tipo][$propriedade])) {continue;}
			$matriz_de_propriedades[$tipo][$propriedade]=$valor; // guarda todas as propriedades de todos os tipos de secao
			if ($propriedade == "rotulo" && $pula==0) {$rotulo[$tipo] = $valor; $pula++;} // rotulo que aparece quando eh um campo padronizado (cargo, comissionamento, etc)
			if ($propriedade == "cor_de_fundo" && $pula_cor==0) {$cores[$tipo] = $valor; $pula_cor++;} // rotulo que aparece quando eh um campo padronizado (cargo, comissionamento, etc)
			$descricoes[$tipo] = $descricao3;
			if ($propriedade == "cor_da_fonte" && $pula_cor_fonte==0) {$cor_da_fonte[$tipo] = $valor; $pula_cor_fonte++;} // rotulo que aparece quando eh um campo padronizado (cargo, comissionamento, etc)

			if ($propriedade == "tabela_externa") {$frag_sql_tabela=" from ".$valor." where "; $nome_tabela_externa = $valor; } // se o campo tem tabela externa entao gera fragmento de sql 
			if ($propriedade == "campo_externo") {$frag_sql_select="select ".$valor; $nome_campo_externo = $valor;} // 
			if ($propriedade == "id_chave_externa") {$frag_sql_select_id="select ".$valor; $ifnull=" IFNULL(("; $titulo_ifnull="), 'variavel_titulo_indisponivel') ";} // 
			if ($propriedade == "id_chave_externa") {$frag_sql_where=$valor."= 'variavel_titulo_indisponivel';"; $nome_id_chave_externa = $valor;} // 

			if ($propriedade == "tabela_externa") {$frag_sql_insert_tabela="INSERT INTO versoes (id_secao, trecho) VALUES (NULL, ";} // rotulo que aparece quando eh um campo padronizado (cargo, comissionamento, etc)
//        INSERT INTO versoes(id_secao, trecho) VALUES (LAST_INSERT_ID(), in_descricao);
				
//			retorna_style($propriedade, $valor, $id_secao);
			// echo "<script>alert('".$style."');</script>";

		}
//		file_put_contents("debugue_matriz_propriedades.txt", json_encode($matriz_de_propriedades)." (opa)\n", FILE_APPEND);

	} // if results


} //fim termina_de_completar

function percorre_tipos_secoes(){
global $matriz_de_propriedades;

include "identifica.php.cripto";

$database = $nome_base_dados;

$sql = "call mostra_arvore_niveis_tipos_secoes_com_pai();";

$conn2= new mysqli("localhost", $username, $pass, $database);

// $top_folha_ts e demais _ts estao definidos em index.php

$result=$conn2->query("$sql");
$conta=0;
$conta_colunas=0;
$arvore_tipos = "";
if ($result->num_rows>0) {
    while($row=$result->fetch_assoc()){
        $id             = $row["id_tipo_secao"];
        $nome           = $row["nome_secao_tipo_secao"];
        $nivel          = $row["nivel"];
    	$pai            = $row["pai"];
	// echo $nome."<br>";
    termina_de_completar_tabela_propriedades($nome, $username, $pass, $database); // coloca o resto das propriedades que ainda nao foram carregadas
	} // while

} // if
file_put_contents("debugue_matriz_propriedades_final.txt", json_encode($matriz_de_propriedades));
}

percorre_tipos_secoes();

?>
