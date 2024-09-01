<?php
// o presente código foi criado para encontrar os arquivos que são executados no arquivo cria_database.bash e mostrar como uma página web. O conteúdo dos arquivos chamados pelo cria_database.bash é mostrado na página web, mas de forma resumida, apenas para facilitar o entendimento de como a base de dados do papedins é criada. 

$rootDir = '/var/www/html/dev_vitor/papedins'; // diretório raiz
$arquivo_de_cria_database = 'cria_database.bash'; // arquivo que contém os comandos para criar a base de dados


function showDatabaseTables($conn) {
    global $nome_base_dados;
    // Consulta para pegar todas as tabelas do banco de dados
    $sql = "SHOW TABLES";
    $result = $conn->query($sql);

    // Arrays para guardar as tabelas e suas chaves estrangeiras
    $tables = [];
    $tablesWithFK = [];
    $fks = [];

    while ($row = $result->fetch_row()) {
        $tableName = $row[0];
        $tables[$tableName] = [];

        // Verificando as chaves estrangeiras de cada tabela
        $fkSql = "SELECT COLUMN_NAME, REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME
                  FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
                  WHERE TABLE_SCHEMA = '".$nome_base_dados."' AND TABLE_NAME = '$tableName' AND REFERENCED_TABLE_NAME IS NOT NULL";
        $fkResult = $conn->query($fkSql);
        while ($fkRow = $fkResult->fetch_assoc()) {
            $tables[$tableName][$fkRow['COLUMN_NAME']] = $fkRow['REFERENCED_TABLE_NAME'] . ' (' . $fkRow['REFERENCED_COLUMN_NAME'] . ')';
            $tablesWithFK[$tableName] = true;
            // Associar também a tabela referenciada para estabelecer uma relação de dependência
            $fks[$fkRow['REFERENCED_TABLE_NAME']][] = $tableName;
        }

        if (!isset($tablesWithFK[$tableName])) {
            // Marcar a tabela como sem FKs
            $tablesWithFK[$tableName] = false;
        }
    }

    // Criando o div container
    echo '<div id="dbContainer">';

    // Organizando as tabelas por nível de dependência de chave estrangeira
    $processedTables = [];
    $currentLevelTables = array_keys(array_filter($tablesWithFK, function($hasFK) { return !$hasFK; }));
    $conta_tableLevel = 0;
    while (!empty($currentLevelTables)) {
        echo '<div class="tableLevel"><div class="titulo_tableLevel">'.($conta_tableLevel=== 0 ? 'Tabelas sem chaves estrangeiras' : 'Tabelas com chaves estrangeiras '.$conta_tableLevel)."</div>";
	$conta_tableLevel++;
        foreach ($currentLevelTables as $table) {
            echo '<div class="tableContainer" id="tableContainer_'.$table.'" data-retorno="retornar_'.$table.'"><strong>' . $table . '</strong>';
            if (!empty($tables[$table])) {
                echo '<div class="fkContainer">';
                foreach ($tables[$table] as $column => $refTable) {
                    echo '<div class="fkItem" id="fkItem_'.trim(preg_replace('/\(/','_',preg_replace('/\)/','',trim($refTable)))).'" data-companion="tableContainer_'.trim(preg_replace('/\(.*$/','',$refTable)).'" data-pai="tableContainer_'.$table.'">' . htmlspecialchars($column) . ' → ' . htmlspecialchars($refTable) . '</div>';
                }
                echo '</div>';
            }
            echo '<a class="retornar" id="retornar_'.$table.'" data-pai="tableContainer_'.$table.'">Retornar</a><input class="botao_mostra" type="button" value="mostra" onclick="fetchData(`'.$table.'`,-1)" ></div>';
        }
        echo '</div>';

        // Encontrar tabelas com FKs que referenciam qualquer uma das tabelas no nível atual
        $nextLevelTables = [];
        foreach ($currentLevelTables as $table) {
            if (isset($fks[$table])) {
                foreach ($fks[$table] as $childTable) {
                    if (!in_array($childTable, $processedTables)) {
                        $nextLevelTables[] = $childTable;
                    }
                }
            }
        }
        $currentLevelTables = array_unique($nextLevelTables);
        $processedTables = array_merge($processedTables, $currentLevelTables);
    }

    echo '</div>';
}



include "identifica.php.cripto";
// Define o tipo de conteúdo como "text/html"
header('Content-Type: text/html; charset=utf-8');

// Imprime o cabeçalho da página
echo "<!DOCTYPE html>";
echo "<html>";
echo "<head>";
echo "<meta charset='UTF-8'>";
echo "<title>Estrutura do Banco de Dados</title>";
echo "</head>";
echo "

<style>

body {
    margin: 0;
    padding: 0;
    font-family: Arial, sans-serif; /* Define uma fonte genérica */
    font-size: 16px; /* Define um tamanho de fonte padrão */
    line-height: 1.5; /* Define uma altura de linha padrão */
    background-color: #ffffff; /* Define uma cor de fundo padrão */
    color: #000000; /* Define uma cor de texto padrão */
}

/* Estilo para links não visitados */
a:link {
  color: white; /* Cor antes de clicar */
}

/* Estilo para links visitados */
a:visited {
  color: yellow; /* Cor depois de clicar */
}

table, th, td {
	color: black;
    border-collapse: collapse;
    width: 100%;
    border: 1px solid #FFFFFF;
    margin: 10px;
    padding: 3px;
}


.retornar {
	font-size: 0.7rem;
    display: block;
    margin-top: 5px;
    text-align: right;
    text-decoration: none;
    color: #0000FF;
    visibility: hidden;
}

.retornar:link {
  color: blue; /* Cor antes de clicar */
}

/* Estilo para links visitados */
.retornar:visited {
  color: black; /* Cor depois de clicar */
}

.arquivo {
    display: flex;
    margin: 10px;
    padding: 10px;
    border: 1px solid #000000;
    background-color: #000050;
}
.lista_tabelas {
    display: flex;
    flex-wrap: wrap;
    margin: 10px;
    padding: 10px;
    border: 1px solid #000000;
    background-color: #000050;
}
.nome_tabela {
    flex: 1;
    margin: 10px;
    padding: 10px;
    border: 1px solid #000000;
    background-color: #001530;
    color: #FFFFFF;
    border-radius: 10px;
    border: 1px solid #2020FF;
}

.tabela {
    display: flex;
    flex-wrap: wrap;
    justify-content: space-around;
    border-radius: 10px;
    align-items: flex-start;
    align-content: flex-start;
    margin: 10px;
    padding: 10px;
    border: 1px solid #000000;
    background-color: #0000aa;
}

.caption_tabela {
	font-size: 2rem;
	font-weight: bold;
	color: grey;
}

.sql {
    display: flex;
    font-size: 1.1rem;
    font-weight: bold;
    flex-wrap: wrap;
    justify-content: space-around;
    border-radius: 10px;
    align-items: flex-start;
    align-content: flex-start;
    margin: 10px;
    padding: 10px;
    border: 1px solid #000000;
    background-color: rgb(200, 115, 0);
}

.comando {
    margin: 10px;
    padding: 10px;
    border: 1px solid #000000;
    background-color: #a0a0a0;
    border-radius: 10px;
}
.descricao {
    margin: 10px;
    border-radius: 10px;
    padding: 10px;
    font-size: 1.1rem;
    max-height: 20vh;
    overflow-y: scroll; /* Adiciona barras de rolagem quando o conteúdo ultrapassa o tamanho do item */
    border: 5px solid #000000;
    background-color: rgb(200, 115, 0);
    box-sizing: border-box; /* Inclui margens e preenchimento no tamanho total do item */
}
.descricao:nth-child(odd) {
    order: 1; /* Coloca os itens ímpares primeiro na ordem de apresentação */
}

.titulo {
    flex-grow: 0;
    flex-shrink: 1;
    font-size: 20px;
    font-weight: bold;
    text-align: left;
    margin: 5px;
    padding: 10px;
    border-radius: 10px;
    border-bottom: 1px solid #000000;
    background-color: #808080;
}
.titulo_geral {
    flex-grow: 0;
    flex-shrink: 1;
    font-size: 3rem;
    font-weight: bold;
    text-align: left;
    margin: 5px;
    padding: 10px;
    border-radius: 10px;
    border-bottom: 1px solid #000000;
    background-color: black;
    color: yellow;
}
.texto_geral {
    flex-grow: 0;
    flex-shrink: 1;
    font-size: 1rem;
    text-align: left;
    margin: 5px;
    padding: 10px;
    border-radius: 10px;
    border-bottom: 1px solid #000000;
    background-color: black;
    color: yellow;
}
.comentario {
    flex-grow: 0;
    flex-shrink: 1;
    font-size: 1rem;
    text-align: left;
    margin: 5px;
    padding: 10px;
    border-radius: 10px;
    border-bottom: 1px solid #000000;
    color: black;
    font-weight: normal;
    background-color: #A0A000;
    max-width: 20vw;
}
.campos {
    text-align: left;
    margin: 5px;
    margin-top: 0px;
    padding: 5px;
    border-radius: 5px;
}
.matched {
    flex-grow: 0;
    flex-shrink: 1;
    font-size: 1rem;
    font-weight: normal;
    text-align: left;
    margin: 5px;
    padding: 10px;
    border-radius: 10px;
    border-bottom: 1px solid #000000;
    color: black;
    background-color: #006000;
    max-width: 20vw;
}
#tabela_alteracoes {
    border-collapse: collapse;
    width: 100%;
    border: 1px solid #FFFFFF;
    margin: 10px;
    padding: 3px;
}

#tabela_alteracoes th {
    background-color: #FFFFFF;
    color: black;
    font-weight: bold;
    border: 1px solid #FFFFFF;
    padding: 3px;
}

td {
    border: 1px solid #FFFFFF;
    padding: 3px;
    color: white;
    vertical-align: top;
}

p+ .comando {
    margin-top: 10px;
    background-color: #a0a0a0;
    color: black;
    font-family: 'Roboto Mono', monospace;
}

#dbContainer {
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    margin: 20px;
}

.tableLevel {
    display: flex;
    flex-wrap: wrap;
    margin-bottom: 20px;
    padding: 10px;
    border: 1px solid #666; 
    background-color: #19f9a9;
}

.tableContainer {
    margin-right: 20px;
    margin-bottom: 20px;
    padding: 10px;
    border: 1px solid #666;
    background-color: #f9f9f9;
    width: 300px;
    overflow: hidden;
    border-radius: 5px;
}

.tableContainer strong {
    display: block;
    font-weight: bold;
    border-bottom: 1px solid black;
    margin-bottom: 5px;
}

.fkContainer {
    font-size: 0.9em;
    overflow-x: auto;
}

.fkItem {
    white-space: nowrap;
}

.titulo_tableLevel {
    font-size: 1.2em;
    font-weight: bold;
    margin-bottom: 10px;
    width: 100%;
}

@keyframes blinking {
    0%, 100% { opacity: 1; }
    50% { opacity: 0; }
}

.blink {
    animation: blinking 0.5s infinite;
}
@keyframes flashing {
    0%, 100% { background-color: blue; }
    50% { background-color: yellow; }
}

.flash {
    animation: flashing 0.5s infinite;
}


.fkItem {
    cursor: pointer;
    transform: scale(0.9); /* Reduz o tamanho do item para parecer menor */
    transition: transform 0.3s ease; /* Suaviza a transição do efeito de escala */
}

.fkItem:hover {
    transform: scale(1); /* Retorna ao tamanho normal quando o mouse está sobre o item */
}
#loading {
    position: fixed;
    top: 0;
    left: 0;
    width: 100vw;
    height: 100vh;
    background-color: rgba(0, 0, 0, 0.5);
    color: white;
    display: flex;
    visibility: visible;
    align-items: center;
    justify-content: center;
    font-size: 2em;
    z-index: 10000;
}
#resultado {
    display: none;
    flex-direction: column;

    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    background-color: white;
    border: 1px solid black;
    padding: 10px;
    z-index: 100000;
    color: black;
    background-color: #f9f9f9;
    border-radius: 5px;
    max-width: 80vw;
    max-height: 80vh;
    overflow: auto;
background-color: #CCCCCC;
}

.tabela_resultado * {
	color: black;
	background_color: gray;
	border: 1px solid black;
	border-collapse: collapse;
width: 90%;
                box-sizing: border-box;
}

</style>";
echo "<body>

 <!--   <div id='loading' class='blink' style='position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(0, 0, 0, 0.5); color: white; display: flex; align-items: center; justify-content: center; font-size: 2em; z-index: 1000;'>Carregando...</div> -->
    <div id='resultado'></div>
<div class='arquivo'><div class='titulo_geral'>Descrição do Conteúdo de ".$arquivo_de_cria_database."

<div class='texto_geral'>Esta página traz uma descrição sucinta de todos os comandos que são executados pelo arquivo ".$arquivo_de_cria_database.". O conteúdo dos arquivos chamados pelo ".$arquivo_de_cria_database." é mostrado na página web, mas de forma resumida, apenas para facilitar o entendimento de como a base de dados do papedins é criada. <br> <br>Dentre as informações estão: <br> <br>1. Nome do arquivo que é executado; <br>2. Tabelas manipuladas e suas colunas; <br>3. Comandos SQL executados e o nome da tabela em que são aplicados. <br> <br>Assim, os arquivos chamados pelo ".$arquivo_de_cria_database." estão indicados abaixo, logo após a lista de tabelas presentes na base dados. Importante notar que todas as tabelas e stored procedures que têm a sigla fc_ referem-se à hierarquia da Fundacentro.<br> <br>
Existem dois aplicativos de árvore, um que pode ser chamado pelo link Hierarquiton (Hierarquia da Fundacentro) e o que pode ser chamado pelo link Fomindica (que mostra a estrutura de indicadores). O Hierarquiton fica no diretório fcentro3/src/html e fcentro3/src/php, ao passo que o Fomindica fica no diretório src/html_arvore e src/php_arvore. Quando o usuário clica em grava_backup_sql no Hierarquiton o arquivo é gravado em papedins/fcentro3/src/php. Quando é apertado em Formindica grava em papedins/src/php_arvore. O nome do arquivo gravado nos dois diretórios é sempre script_SQL_AAAA_MM_DD_HH_MM_SS.sql.  <br> <br> Para que tenham efeito, esses arquivos devem ser copiados para papedins/src/sql, mas com nomes diferentes. O arquivo gerado pelo hierarquiton deve ser copiado em script_fc.sql. O outro eu não lembro agora, mas basta verificar em cria_database.bash.
<p>Se você gerou uma nova estrutura sintática de indicador através do FORMINDICA acionando ../html_arvore/index.php, você gerar o script através do botão grava_backup_sql. Um arquivo será criado devendo ser copiado para o diretório sql como o comando a seguir:</p> <div class='comando'>cp ../php_arvore/script_SQL_2024_05_04_13_51_53.sql script_SQL_padrao.sql</div>
<p>O GERINDICA, que é chamado pelo código ../html/ppapdi.php, gera automaticamente um script de recuperação chamado <b>script_recuperacao_AAAAMMDD-HHMMSS.sql</b>. Para gerar o banco de dados papedins_db com o código <b>cria_database.bash</b> é preciso antes copiar esse script de recuperação da seguinte forma:</p><div class='comando'>cp ../html/script_recuperacao_20240504-152016.sql script_recuperacao_padrao.sql</div>
</div>


</div>

<div class='texto_geral'>
Log de alterações:
<table id='tabela_alteracoes'>
<tr>
<th>id</th><th>data</th><th>resp.</th><th>alteração</th><th>status</th>
</tr>
<tr>
<td>1</td><td>2024-05-02</td><td>VPM</td><td>Criação do diretório php_inseridencia, que contem uma nova versão de inseridencia.php, mas voltada para o registro de evidências construídas na forma de uma frase com verbo na primeira pessoa do singular (passado), substantivo e adjetivo. </td><td>O link para inseridência presente em ./papedins/src/html/index.html já foi alterado para acionar inseridencia.php presente no diretório ./papedins/src/php_inseridencia. VPM 20240718: acabei criando um novo diretorio com o mesmo objetivo chamdo php_inseridencia_frases a partir de php_inseridencia usando rsync, apenas para ter claro no nome que usaremos inseridencia baseado em frases, mantendo php_inseridencia como backup</td>
</tr>
<tr>
<td>2</td><td>2024-05-02</td><td>VPM</td><td>Criação de uma tabela evidencias_via_frases, que contém os mesmos campos de evidências, acrescidos de id_token_verbo, id_token_substantivo e id_token_adjetivo. O verbo deverá estar na primeira pessoa do singular no pretérito perfeito (analisei), o substantitvo é qualquer um e o adjetivo é qualquer um também. Serão acrescentados campos livres, que só poderão ser preenchidos com uma única palavra depois que os campos fixos forem, bem como um campo de descrição.</td><td>Ao que tudo indica não foi implementado, segundo inspeção de 04/06/2024. Não é muito funcional esta solução porque confunde o conceito de evidência com o conceito de evento.</td>

</tr>
<tr>
<td>3</td><td>2024-05-02</td><td>VPM</td><td>
<ul>
<li>Conheci os territórios rurais.</li>
<li>Capacitei cooperativas locais.</li>
<li>Cadastrei membros ativos.</li>
<li>Registrei entidades emergentes.</li>
<li>Ofereci apoio técnico.</li>
<li>Levantei dados econômicos.</li>
<li>Criei redes colaborativas.</li>
<li>Propus políticas públicas.</li>
<li>Mapeei recursos disponíveis.</li>
<li>Visitei comunidades distantes.</li>
<li>Organizei oficinas educativas.</li>
<li>Articulei parcerias solidárias.</li>
<li>Monitorei avanços comunitários.</li>
<li>Avaliei necessidades urgentes.</li>
<li>Promovi diálogos inter-entidades.</li>
</ul>
</td><td>em concepção</td>
</tr>

<tr>
<td>4</td><td>2024-05-13</td><td>VPM</td><td>Criação do código insere_tokens_solidarios5.php que permite executar arquivos de tokens nos formatos presentes nos seguintes arquivos de entrada:  
<ul>
<li>frases_otimizadas.txt</li>
<li>frases_substantivos_plurais.txt</li>
<li>frases_substantivos_singulares.txt</li>
<li>frases_revistas_2024_05_13.txt</li>
<li>frases_SST_contabilidade.txt</li>
<li>frases_educacao_popular.txt</li>
<li>frases_formacao_tecnica.txt</li>
</ul>
A chamada de insere_tokens_solidarios5.php é feita através do cria_database.bash, que já chama os 4 arquivos de frases indicados acima.
</td><td>Foi encontrado um arquivo com o nome insere_tokens_solidario6_flex.php o que gerou a dúvida sobre qual seria a última versão. Verificou-se que esse arquivo é idêntico a insere_tokens_solidario5.php que é efetivamente chamado em cria_database.php, com uma única diferença: o insere_tokens_solidario5.php tem uma função a mais: <i>function inserirFrases</i>, que parece ser uma versão antiga de <i>function inserirFrases2</i>. Verificou-se que inserirFrases() nunca é invocada no insere_tokens_solidarios5.php</td>
</tr>

<tr>
<td>5</td><td>2024-06-04</td><td>VPM</td><td>Repensando o que está descrito na alteração 2: Um evento pode ter mais de uma evidência. Portanto, o aplicativo de inserção de evidências (inseridencia) poderia ser diferente, voltado para inserir eventos que, depois, seriam comprovados por evidências. Entretanto, na minha visão, essa abordagem seria anti-natural, uma vez que exigiria do bolsista um foco no evento em que ele está, mas o que queremos dos bolsistas é tão somente que registrem as evidências de suas atividades (e nesse sentido evidências e atividades são termos intercambiáveis no contexto do aplicativo). Ou seja, o usuário deveria ficar exclusivamente com a responsabilidade de entrar com as evidências do que está fazendo em campo, e os eventos deveriam ser criados a partir delas. Assim, o evento poderia ser extraído posteriormente por inferência automática do sistema, a partir do local e horário das evidências. Em outras palavras, mais de uma pessoa colocando evidências no mesmo local e em horários próximos definiriam um evento. Um evento poderia ser definido até mesmo se uma mesma pessoa quisesse colocar mais de uma evidência de atividade em um mesmo local, em horários próximos. Aí vem a questão se o sistema deveria ter uma tabela de eventos na base de dados. Uma hipótese é que o evento poderia ser um atribuído às evidências a posteriore de forma automática pelo sistema, a partir de parâmetros de proximidade horário e de localização das evidências. Se for definido automaticamente a partir de parâmetros aplicados a posteriore, o número de eventos pode mudar de acordo com os parâmetros aplicados e pode-se perder nuances como dois eventos diferentes ocorrendo no mesmo local. Isso criaria uma perda de informações e um instabilidade, mas também poderia se tornar uma forma flexível de olhar os indicadores produzidos pelo projeto, permitindo um ajuste da qualidade dos indicadores de resultados a posteriore. Outra possibilidade é deixar o usuário entrar com a primeira evidência, atribuindo um nome ao evento que seria gravado numa tabela eventos, e, através de um botão de 'adicionar evidência', permitir o acréscimo de mais um registro de evidência para o mesmo registro da tabela eventos. A questão é que se o usuário fechar a evidência atual, abrindo uma nova, não haverá o reconhecimento do sistema de que aquela evidência se refere ao evento anterior, criando um novo evento. Uma questão sobre ter múltiplas evidências para um mesmo evento é que teremos múltiplos identificadores e múltiplos indicadores. Se pensarmos na evidência como 'uma evidência de atividade que leva um resultado', poderemos ter eventos com mais de um resultado, o que é adequado mas complexo, porque cada evento pode ter uma combinação de indicadores e identificadores oriunda das diversas evidências registradas (atividades realizadas). Podemos pensar em um conjunto de resultados para o evento que, por sua vez, é um conjunto disjunto dois a dois de atividades comprovadas por evidências e identificadas por frases padronizadas (provavelmente verbo-substantivo-adjetivo).</td><td>Proposta de atuação: criar uma tabela eventos, para guardar um evento para o qual N evidencias apontam. Quando uma nova evidência é criada, um novo evento é criado. Se o usuário desejar criar novas evidências enquanto ainda não guardou a primeira evidência, todas as novas evidências estarão referenciadas ao evento ao qual está referenciado a primeira evidência. Se o usuário guardar a evidência recém criada, ou o conjunto de evidências associados a ela, todas serão referentes ao mesmo evento, mas a próxima evidência será referente a um novo evento. O nome do evento será sempre o nome dado à primeira evidência do conjunto. Quando uma nova evidência é criada nas imediações geográficas e num período de tempo que essa evidência possa ser considerada de algum evento já registrado, o usuário será perguntado se está tentando entrar uma evidência referente a um evento já existente. O sistema pode definir parâmetros de tempo e distância para definir se vai fazer essa pergunta, considerando que um evento aconteceu há menos de 1 hora, por exemplo e, portanto a nova evidência pode se referir a ele. A questão é o conjunto de identificadores e indicadores da evidência. Se tivermos um conjunto de identifica. Se considerarmos que as evidências se referem a atividades que produzem um resultado (evidências de atividades), a coisa pode funcionar </td>
</tr>
<tr>
<td>6</td>
<td>2024-06-12</td>
<td>VPM</td>
<td>É preciso organizar as frases que representam indicadores em subconjuntos de resultados. A SENAES apresentou um modelo lógico que fala em 5 tipos de resultados, que por sua vez são organizados em até 8 tipos de sub-resultados, formando uma árvore.</td>
<td>Criaremos uma tabela 'tipos_de_resultados' para representar a árvore de tipos_de_resultados, mas não utilizaremos a estrutura de 'nested tree'. Usaremos um campo com link para o pai porque já temos duas instâncias de 'nested trees' (hierarquia da Fundacentro) e autômatos de indicadores. Vamos variar um pouco a estrutura e experimentar diferente com tipos_de_resultados.</td>
</tr>
<tr>
<td>7</td>
<td>2024-07-18</td>
<td>VPM</td>
<td>Agora ficou bem claro: a tabela eventos é alimentada independentemente do bolsista estar no local do evento. Essa tabela pode ser preenchida a posteriore ou antecipadamente. O fato de haver um evento nesta tabela não significa que ele ocorreu ou que o bolsista participou dele. Apenas a inserção de dados na tabela evidencias permitirá garantir que houve uma evidência de que o evento ocorreu e o bolsista participou. Quando o bolsista vai inserir uma evidência, ele é perguntado se ele está num evento cujo horário e local tenha sido previamente registrado. O registro prévio pode ser feito a partir do latlong da evidência que está sendo inserido (caso não haja evento ainda no local da evidência), ou manualmente, indicando a cidade do evento. Na inserção de uma evidência, o sistema busca todos os eventos na cidade atual, obtida por latlong e nominatim. Note que antes eu estava trabalhando de que uma única evidência poderia estar ligada a mais de uma frase. Mas estou mudando dessa opinião. Acho que uma evidência deve representar apenas uma frase. Se houver outra atividade (outra frase), o bolsista deve entrar outra evidência. Então cada evidência está ligada a apenas uma frase. Antes, o sistema permitia que cada evidência tivesse mais de um endereço. Acho que essa situação é devida a alguma imprecisão do nominatim? Não sei. Mas, a rigor, cada evidência deve ter apenas um endereço.</td>
<td>Segundo essa visão, a tabela 'evidencias' deve conter uma ÚNICA chave externa para 'frases'. Eu devo guardar o endereço da evidência obtido através do nominatim, ou devo perguntar para o usuário em que evento que ele está, uma vez que o evento tem um endereço? A evidência guarda latlong, mas deve guardar endereço real obtido a partir do nominatim? A solução é: alterar a tabela eventos, para incluir informções de endereço e data do evento (não adianta só timestamp, que é apenas o momento da entrada do dado). No caso da tabela eventos, a data pode ser de um evento que vai acontecer ou que aconteceu. Ou pode ser a data e horário atual. O Horário do evento é importante para que depois seja possível buscar se a evidência refere-se a um evento em andamento. A evidência só pode ser gravada no momento da atividade. Posteriormente podemos até criar um meio de colocar evidências a posteriore. Evidências terá apenas um id para frases e não terá nome de evidência preenchido pelo usuário. O Usuário só indicará qual é a frase (entrega no passado, segundo tipos_elementos_sintaticos) que caracteriza o que ele está fazendo, com um campo de comentários livres. As frases serão classificadas segundo o modelo lógico da SENAES. Existe um risco da frase não representar exatamente o que o que o bolsista está fazendo. Considero a possibilidade de colocar uma descrição para cada frase, facilitando a escolha da frase pelo usuário. O usuário (bolsista) vai digitar o começo do que está fazendo e aparecerá um drop box com as frases mais próximas disponíveis para representar aquela atividade. Ele selecionará a frase. Mas antes de selecionar a frase, quando ele pedir para incluir uma evidência, aparecerá um drop box com todos os endereços de eventos em curso naquele momento, nas imediações da posição atual. Dois tipos de busca de endereços serão feitos: busca pelo lat long, verificando se o lat long está no bounding box de eventos existentes, um busca por cidade e bairro da cidade atual, feita também pelo lat long da evidência. Como os eventos podem ser adicionados manualmente, pode ser que o responsável por inserir o evento desconheça o lat long, entrando apenas a cidade ou, na melhor das hipóteses, o bairro do evento. Por isso, quando uma nova evidência for inserida, é preciso ter a opção de buscar eventos próximos na mesma cidade ou bairro, sem usar o lat long. Por isso, o nominatim terá que ser consultado toda vez que uma nova evidência seja inserida, para que o nome da cidade ou o bairro possam ser usados para encontrar eventos próximos. Talvez a busca por bairro seja um exagero. Mas a data da evidência será usada para cruzar com a vigência do evento. Se a data de uma evidência estiver aproximadamente dentro da vigência de eventos na mesma cidade, será oferecida uma lista de eventos possíveis, para aquele horário e localidade. Se nenhum evento for encontrado, o usuário terá que inserir um novo evento, criando um nome para ele. O bolsista que criou o evento  será registrado na tabela eventos, através de id_pessoa ou equivalente. As evidências também terão o registro de quem estava usuando o aplicativo para criar a evidência. Uma primeiríssima versão desse sistema está em consulta_endereco_atual.php</td>
</tr>
</table>
</div>

<div class='texto_geral'>
<b>Como inserir um novo token:</b><br><br>O processo de inserção de um novo token manualmente passa por várias etapas e pode ser um tanto complexo, porque é necessário atribuir o token a um grupo, indicando, também se ele é uma evidência ou um veículo.<br><br>
Salvo engano, um token não pode ser evidência e veículo ao mesmo tempo. <br><br>
Tudo começa inserindo o token no arquivo <b>insert_tokens.sql</b>. Se o token que se quer inserir for um 'infinitivo' (e.g. documentar, analisar, etc.), basta incluí-lo na seção de infinitivos. Mas se for uma substantivação (e.g. documento, análise, etc.) ou adjetivo (documental, analítico, e.g.), será preciso identificar o radical dessa substantivação ou adjetivação na forma de um infinitivo, inserir esse radical na seção de infinitivos e depois inserir os tokens respectivos nas seções de substantivos e adjetivos (na verdade não existe uma seção exata no arquivo <b>insert_tokens.sql</b>, mas está mais ou menos organizado).<br><br> 
É preciso dizer se o novo token é uma evidência ou um veículo. Isso é feito no arquivo <b>script_tipos_veidencias.sql</b>. Nesse arquivo, você vai inserir o token na tabela tipos_evidencias se for uma evidencia, ou na tabela tipos_veiculos, se for um veiculo.<br><br>
O próximo passo é dizer a qual grupo de tokens o token recém inserido pertence. Isso é feito no arquivo <b>script_update_grupo_padrao.sql</b>, através de um comando update que atualiza o campo id_grupo_de_token da tabela tokens.<br><br>
Acontece que um token pode pertencer a mais de um grupo. Se for esse o caso, é preciso deixar isso claro, o que é feito no arquivo <b>script_cria_n_to_n_grupos_tokens.sql</b>, onde a tabela grupos_vs_tipos_de_evidencias_n_to_n é atualizada, ou a tabela grupos_vs_tipos_de_veiculos_n_to_n é atualizada, dependendo se o token é uma evidência ou um veículo.<br><br>
Se alguma propriedade dos tokens é alterada através das interfaces, tais como a que está em src/html/arvore_de_tokens.php, é preciso atualizar o arquivo respectivo no diretório src/sql manualmente, para que numa próxima geração da base de dados tudo esteja atualizado. <br><br>
Então no caso de <b>script_tipos_veidencias.sql</b>, é preciso abrir a página novamente após definir os checkboxes pertinentes e uma arquivo com dados atualizados será gravado no diretório html. Esse arquivo precisa ser copiado com o nome adequado no diretório sql, sobrepondo o atual que é chamado por cria_database.bash, de forma que a alteração realizada no checkbox esteja perenizada para as próximas criações manuais da base de dados.<br><br>
O mesmo vale para mostra_grupos_de_tokens_duplas_identificadores.php, que tem um botão para gravar o arquivo script_update_valido.sql. Depois de marcar quais duplas de tokens são válidas para o gruopo de tokens em questão, é preciso apertar o botão. Um arquivo atualizado com as escolhas será gravado no mesmo diretório e esse arquivo precisa ser copiado para o diretório sql. <br><br>
Este mesmo procedimento de apertar um botão para atualizar o script gerador da base também é válido para a página que está em src/html/index.php, que é o gerador de estruturas (autômatas) de indicadores. Ali tem um botão que vai gravar o script no mesmo diretório, organizado por data. Esse arquivo deve ser copiado para o diretório sql para que o sistema de atualização da base de dados funcione corretamente.<br><br>
O mesmo é verdade para o arquivo fcentro3/src/html/index.php, que permite editar a estrutura organizacional da Fundacentro. Se essa estrutura for alterada, é preciso atualizar o arquivo respectivo que está no diretório sql. Para fazer essa atualização é preciso apertar o botão que cria uma cópia do script sql e copiar para o diretório sql.<br><br>
</div>";


echo"
</div>
";
$conn = mysqli_connect("localhost", $username, $pass, $nome_base_dados);
if (!$conn) {
    die("Falha na conexão: " . mysqli_connect_error());
}



echo "<div id='id_lista_tabela' class='lista_tabelas'>";
$todas_tabelas = obterTabelas($nome_base_dados);
foreach ($todas_tabelas as $tabela) {
    echo "<div class='nome_tabela'><a href='#tab_$tabela'>$tabela</a></div>";
    $sqlTables[] = $tabela;
}
echo "</div>";

ShowDatabaseTables($conn);

// Exemplo de uso
$conn = mysqli_connect("localhost", $username, $pass, $nome_base_dados);
if (!$conn) {
    die("Falha na conexão: " . mysqli_connect_error());
}

function obterTabelas($nome_base_dados) {
    // Configurações de conexão
    global $nome_base_dados;
    global $username;
    global $pass;
    $servidor = "localhost"; // ou o endereço do servidor MySQL
    $usuario = $username; 
    $senha = $pass;

    // Conexão com o banco de dados
    $conexao = new mysqli($servidor, $usuario, $senha, $nome_base_dados);

    // Verifica se houve erro na conexão
    if ($conexao->connect_error) {
        die("Erro na conexão: " . $conexao->connect_error);
    }

    // Consulta para obter as tabelas
    $query = "SHOW TABLES";

    // Executa a consulta
    $resultado = $conexao->query($query);

    // Verifica se houve erro na execução da consulta
    if (!$resultado) {
        die("Erro na consulta: " . $conexao->error);
    }

    // Array para armazenar as tabelas
    $tabelas = array();

    // Loop para obter o nome de cada tabela
    while ($linha = $resultado->fetch_row()) {
        $tabelas[] = $linha[0];
    }

    // Fecha a conexão
    $conexao->close();
    asort($tabelas);
    // Retorna o array com as tabelas
    return $tabelas;
}


function findPatternInFilesWithLine_exclui($rootDir, $pattern) {
    // Caminho para o arquivo de lista de exclusão
    $exclusionListPath = '/var/www/html/dev_vitor/papedins/lista_de_exclusao_de_arquivos_para_busca.txt';

    // Lê a lista de exclusão de arquivos
    $exclusions = file($exclusionListPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
array_push($exclusions, '/var/www/html/dev_vitor/papedins/src/sql/cria_database.bash');
array_push($exclusions, '/var/www/html/dev_vitor/papedins/src/sql/encontra_arquivos_de_cria_database.php');

    $directoryIterator = new RecursiveDirectoryIterator($rootDir, RecursiveDirectoryIterator::SKIP_DOTS);
    $iterator = new RecursiveIteratorIterator($directoryIterator);
    $matchedFilesWithLines = [];

if (preg_match('/\.([a-zA-Z0-9]+)$/', $pattern, $extensao)) {
    $extensao = $extensao[1]; // Captura a extensão do arquivo
} else {
    $extensao = ''; // Define a extensão como vazia se não houver extensão
} 

    $pattern = preg_replace('/\.[A-Za-z0-9][A-Za-z0-9][A-Za-z0-9][A-Za-z0-9]?/', '', $pattern); // Remove a extensão do arquivo para permitir a busca por arquivos com nomes modificados
    $pattern = preg_replace('/[_]?padrao[_]?/', '', $pattern); // Remove a palavra "padrao" porque essa palavra é usada apenas no diretorio sql



    foreach ($iterator as $file) {
        // Verifica se o caminho do arquivo está na lista de exclusão
        if (in_array($file->getRealPath(), $exclusions)) {
            continue; // Pula para o próximo arquivo se este estiver na lista de exclusão
        }

        // Verifica se o arquivo é do tipo PHP, HTML, Bash (.sh) ou SQL pela extensão
        if ($file->isFile() && preg_match('/\.(php|html|bash|sql)$/i', $file->getFilename())) {
            // Lê o arquivo linha por linha
            $fileContent = file($file->getRealPath());

            foreach ($fileContent as $lineNumber => $lineContent) {
                if (preg_match("/(?<![A-Za-z0-9_])".$pattern."[^\s]*\.".$extensao."/", $lineContent)) {
                    // Adiciona o caminho do arquivo e a linha correspondente ao array
                    $matchedFilesWithLines[] = ['file' => $file->getRealPath(), 'line' => $lineContent];
                }
            }
        }
    }

    return $matchedFilesWithLines;
}


function findPatternInFilesWithLine($rootDir, $pattern) {
    $directoryIterator = new RecursiveDirectoryIterator($rootDir, RecursiveDirectoryIterator::SKIP_DOTS);
    $iterator = new RecursiveIteratorIterator($directoryIterator);
    $matchedFilesWithLines = [];

    foreach ($iterator as $file) {
        // Verifica se o arquivo é do tipo PHP, HTML, Bash (.sh) ou SQL pela extensão
        if ($file->isFile() && preg_match('/\.(php|html|bash|sql)$/i', $file->getFilename())) {
            // Lê o arquivo linha por linha
            $fileContent = file($file->getRealPath());

		//echo $file.") ".$pattern."<br>";
            foreach ($fileContent as $lineNumber => $lineContent) {
                if (preg_match("/".$pattern."/", $lineContent)) {
                    // Adiciona o caminho do arquivo e a linha correspondente ao array
                    $matchedFilesWithLines[] = ['file' => $file->getRealPath(), 'line' => $lineContent];
                }
            }
        }
    }

    return $matchedFilesWithLines;
}

function getTableInfo($conn, $tableName) {
    // Consulta para obter informações da tabela
    global $nome_base_dados;
    $query = "SELECT COLUMN_NAME, COLUMN_TYPE, COLUMN_KEY
              FROM INFORMATION_SCHEMA.COLUMNS
              WHERE TABLE_NAME = '$tableName' AND TABLE_SCHEMA = '$nome_base_dados'";

    // Executa a consulta
    $result = mysqli_query($conn, $query);

    if (!$result) {
        die("Erro na consulta: " . mysqli_error($conn));
    }

    // Array para armazenar as informações da tabela
    $tableInfo = array();

    // Preenche o array com as informações
    while ($row = mysqli_fetch_assoc($result)) {
        $columnName = $row['COLUMN_NAME'];
        $columnType = $row['COLUMN_TYPE'];
        $isPrimaryKey = ($row['COLUMN_KEY'] == 'PRI') ? true : false;

        // Consulta para verificar se a coluna é uma chave estrangeira
        $fkQuery = "SELECT REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME
                    FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
                    WHERE TABLE_NAME = '$tableName' 
                    AND COLUMN_NAME = '$columnName' AND REFERENCED_TABLE_SCHEMA IS NOT NULL AND TABLE_SCHEMA = '$nome_base_dados'";

        // Executa a consulta para verificar as chaves estrangeiras
        $fkResult = mysqli_query($conn, $fkQuery);

        if (!$fkResult) {
            die("Erro na consulta de chave estrangeira: " . mysqli_error($conn));
        }

        // Verifica se a coluna é uma chave estrangeira e obtém o nome da tabela referenciada
        $referencedTable = null;
        if (mysqli_num_rows($fkResult) > 0) {
            $fkRow = mysqli_fetch_assoc($fkResult);
            $referencedTable = $fkRow['REFERENCED_TABLE_NAME'];
	    $referencedColumn = $fkRow['REFERENCED_COLUMN_NAME'];
        } else
	{
            $referencedTable = "";
	    $referencedColumn = "";
	}

        // Adiciona as informações da coluna ao array de informações da tabela
        $tableInfo[] = array(
            'column_name' => $columnName,
            'column_type' => $columnType,
            'is_primary_key' => $isPrimaryKey,
            'referenced_table' => $referencedTable,
	    'referenced_column' => $referencedColumn
        );
    }

    return $tableInfo;
}

function imprime_dados($conn, $tableName) {
// Exibir informações da tabela
$tableInfo = getTableInfo($conn, $tableName);

//echo "Informações da Tabela $tableName: <br>";
foreach ($tableInfo as $column) {
    echo "<b>".$column['column_name'] . ":</b> ";
    echo $column['column_type'] . "";
    echo ($column['is_primary_key'] ? ', chave primária' : '');
    if ($column['referenced_table']) {
        echo ", foreign key(" . $column['referenced_table'] .", ".$column['referenced_column'] .");<br>";
    } else {
        echo ";<br>";
    }
}
}

// Função para remover os blocos de stored procedures do conteúdo do arquivo
function removeStoredProcedures($fileContent) {
    // Expressão regular para identificar blocos de stored procedures
    $pattern = '/CREATE\s+PROCEDURE\s+\w+\s*\([^)]*\)\s+BEGIN\s+.*?END\s*;/is';

    // Remover os blocos de stored procedures do conteúdo do arquivo
    return preg_replace($pattern, '', $fileContent);
}

function extractSqlCommands($fileName) {
    global $sqlTables;
    // Ler o conteúdo do arquivo
    $fileContent = file_get_contents($fileName);
     $fileContent = removeStoredProcedures($fileContent);   
    file_put_contents('temp_'.$fileName.'.sql', $fileContent);
    // Expressões regulares para os comandos SQL
    $patterns = array(
        '/\bINSERT INTO (\w+)\b/i',
        '/\bSELECT (.*?) FROM ([a-zA-Z0-9_,\s]+)\b/i',
        '/\bUPDATE (\w+)\b/i',
        '/\bCREATE TABLE (\w+)\b/i',
        '/\bDROP TABLE IF EXISTS (\w+)\b/i',
        '/\bDELETE FROM (\w+)\b/i'
    );

    // Array para armazenar pares de comando e tabela
    $sqlCommands = array();
    // Iterar sobre os padrões de expressão regular
    foreach ($patterns as $pattern) {
        // Encontrar todas as correspondências no conteúdo do arquivo
        preg_match_all($pattern, $fileContent, $matches);
        
        // Iterar sobre as correspondências encontradas
	// Iterar sobre as correspondências encontradas

        if ($pattern == $patterns[1]) {
	foreach ($matches[0] as $match) {
	    // Extrair o comando SQL e a tabela
//	echo "matches[0] = ".$match."\n";
	    preg_match('/\bSELECT (.*?) FROM\s+([a-zA-Z0-9_]+)[\s"]?\b/i', $match, $commandTableMatch);
	    
	    // Formar o par de comando e tabela
	    $commandTable = 'SELECT ' . strtolower($commandTableMatch[2]);

	    // Armazenar a tabela se ainda não estiver presente
	    if (!in_array($commandTableMatch[2], $sqlTables)) {
	        $sqlTables[] = $commandTableMatch[2];
	    }
	    
	    // Armazenar o par de comando e tabela se ainda não estiver presente
	    if (!in_array($commandTable, $sqlCommands)) {
	        $sqlCommands[] = $commandTable;
	    }
	} // foreach


	}

	else {

	foreach ($matches[0] as $match) {
	    // Extrair o comando SQL e a tabela
	    preg_match('/\b(INSERT INTO|SELECT .*? FROM|UPDATE|CREATE TABLE|DROP TABLE IF EXISTS|DELETE FROM)\s+(\w+)\b/i', $match, $commandTableMatch);
	    
	    // Formar o par de comando e tabela
	    $commandTable = strtolower($commandTableMatch[1]) . ' ' . strtolower($commandTableMatch[2]);

	    // Armazenar a tabela se ainda não estiver presente
	    if (!in_array($commandTableMatch[2], $sqlTables)) {
	        $sqlTables[] = $commandTableMatch[2];
	    }
	    
	    // Armazenar o par de comando e tabela se ainda não estiver presente
	    if (!in_array($commandTable, $sqlCommands)) {
	        $sqlCommands[] = $commandTable;
	    }
	} // foreach
	} // else

       // foreach ($matches[1] as $tableName) {
       //     // Armazenar o par de comando e tabela se ainda não estiver presente
       //     if (!in_array($tableName, $sqlCommands)) {
       //         $sqlCommands[] = $tableName;
       //     }
       // }
    }

    // Retornar os pares de comando e tabela únicos
    return $sqlCommands;
}

// Array para armazenar todos os pares de comando e tabela únicos
$uniqueSqlCommands = array();
$sqlTables = array();

// Ler o conteúdo do arquivo cria_database.bash
$fileContent = file_get_contents($arquivo_de_cria_database);

// Realizar a filtragem usando expressões regulares
preg_match_all('/^\s*mysql -u root -p[^"]* .*?<\s*[^[:space:]]+\.sql\s*#?.*|[^#]\bphp\s+\w+\.php\b\s*#?.*|\.\/[^[:space:]]+\.bash\s*#?.*/mi', $fileContent, $matches);




$comentario = array();
$comando_inteiro = array();
// Filtrar o resultado para obter apenas os nomes de arquivo
$fileNames = array_map(function($match) {
    global $comentario;
    global $comando_inteiro;
    preg_match('/(#.*)/',$match, $comentario_itz);
    $itz = preg_replace('/\s*#.*$/m', '', $match);
    $itz = preg_replace('/\s?>.*/m', '', $itz);
    $itz = preg_replace('/\s*php\s/m', '', $itz);
    $itz = preg_replace('/^\.\//m', '', $itz);
    $itz = preg_replace('/[A-Za-z0-9_\s-]+<\s*/', '', $itz);
    $comando_inteiro[$itz] = $match;
    if ($comentario_itz) {
	$comentario[$itz] = $comentario_itz[1];
    } else {
	$comentario[$itz] = "sem comentários";
    }
     return $itz; 
     }, $matches[0]);


// Imprimir os nomes dos arquivos filtrados
$conta_arquivos = 0;
foreach ($fileNames as $fileName) {
$matchedFilesWithLines = findPatternInFilesWithLine_exclui($rootDir, $fileName); 
// retorna um array com os arquivos que contém a string $fileName, bem como a linha (line) em que a string foi encontrada
$conta_arquivos++;

    echo "<div class='arquivo'><div class='titulo'>".$conta_arquivos.") ".$fileName . "<div class='comentario'><b>Comentário:</b><br>".$comentario[$fileName]."</div><div class='comentario'><b>Ocorrência em ".$arquivo_de_cria_database.":</b><br>".preg_replace("/#.*/","",$comando_inteiro[$fileName])."</div>";

foreach ($matchedFilesWithLines as $matchedFileWithLine) {
echo "<div class='matched'><b>Arquivo também é referido em:</b><br><b>".$matchedFileWithLine['file'] . "</b><br><br>" . $matchedFileWithLine['line'] . "</div>";


}

echo "</div>";
    unset($sqlTables);
    $sqlTables = array();
    $sqlCommands = extractSqlCommands($fileName);
    unset($uniqueSqlCommands);    
    $uniqueSqlCommands = array();
    // Adicionar os pares únicos ao array final
    $uniqueSqlCommands = array_merge($uniqueSqlCommands, $sqlCommands);
    echo "<br><div class='tabela'><span class='caption_tabela'>Tabelas:</span><br>";
    asort($sqlTables);
	foreach ($sqlTables as $table) {
	    echo "<div id='tab_$table' class='descricao' onclick='document.getElementById(`id_lista_tabela`).scrollIntoView();'><b><div class='titulo'>".$table . "</div></b>";
            echo "<div class='campos'>";
		imprime_dados($conn, $table);
            echo "</div> <!-- campos --><br>"; 
	    echo "</div> <!-- descricao --><br>";
	}
    echo "</div> <!-- tabela --><br>";
    echo "<br><div class='comando'>Comandos:<br>";
    
    asort($uniqueSqlCommands);
	foreach ($uniqueSqlCommands as $commandTable) {
	    echo "<div class='sql'>".$commandTable . "</div>";
	}
    echo "</div> <!-- comando --><br>";
    echo "</div> <!-- arquivo ".$fileName." --><br>";
}

echo "</body>";
echo "<script>
document.addEventListener('DOMContentLoaded', function() {
    var fkItems = document.querySelectorAll('.fkItem');
    var retornars = document.querySelectorAll('.retornar');
//    document.getElementById('loading').style.display = 'flex';

    fkItems.forEach(function(fkItem) {
        fkItem.addEventListener('mousemove', function() {
            var companionId = this.getAttribute('data-companion');
            var companionElement = document.getElementById(companionId);
            if (companionElement) {
                companionElement.classList.add('blink');
            }
        });

        fkItem.addEventListener('mouseleave', function() {
            var companionId = this.getAttribute('data-companion');
            var companionElement = document.getElementById(companionId);
            if (companionElement) {
                companionElement.classList.remove('blink');
            }
        });
    });

    retornars.forEach(function(retornar) {
        retornar.addEventListener('click', function() {
		this.style.visibility = 'hidden';
		document.getElementById(this.getAttribute('data-pai')).classList.remove('flash');
		document.getElementById(this.getAttribute('data-pai')).classList.remove('blink');
		let retornado= document.getElementById(this.getAttribute('data-id-retorno'));
		retornado.classList.add('flash');
		setTimeout(function () {retornado.classList.remove('flash');}, 5000);
	});
    });


    fkItems.forEach(function(fkItem) {
        fkItem.addEventListener('click', function() {
            var companionId = this.getAttribute('data-companion');
	    console.log('este.id = '+this.id);
            var companionElement = document.getElementById(companionId);
            var retornoId = companionElement.getAttribute('data-retorno');
	    var retornoElement = document.getElementById(retornoId);
            if (companionElement) {
                companionElement.scrollIntoView({behavior: 'smooth', block: 'start'});
                companionElement.classList.remove('blink');
                companionElement.classList.add('flash');
            } 
	    if (retornoElement) {
		retornoElement.style.visibility = 'visible';
	        retornoElement.href = '#'+this.getAttribute('data-pai');
		retornoElement.setAttribute('data-id-retorno', this.getAttribute('data-pai'));
	    }
        });
    });
});

// CSS para o efeito de blinking

//window.onload = function() {
//    document.getElementById('loading').style.display = 'none';
//}

function fetchData(tabela, n_registros) {
    var xhr = new XMLHttpRequest();
    xhr.open('POST', 'mostra_tabela_parcial3.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.onreadystatechange = function() {
        if (xhr.readyState == 4 && xhr.status == 200) {
            document.getElementById('resultado').innerHTML = xhr.responseText;
            document.getElementById('resultado').style.display = 'flex';
//	    alert('voltou:'+xhr.responseText);
	} else {
	    console.log('Erro na requisição');
        }
    };
    xhr.send('tabela=' + tabela + '&n_registros=' + n_registros);
}

function closeResult() {
    document.getElementById('resultado').style.display = 'none';
}

</script>";
echo "</html>";

mysqli_close($conn);

