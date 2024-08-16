<?php

session_start();

if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] == true && isset($_GET['hash']) && isset($_GET['nome_usuario'])) {
	session_unset();  // Limpa todas as variáveis de sessão
    session_destroy();  // Destrói a sessão

    // Opcional: certifique-se de que o cookie de sessão seja removido
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }

    // Inicia uma nova sessão
    session_start();
    session_regenerate_id(true);  // Gera um novo ID de sessão para a nova sessão
}

	if (isset($_GET['hash'])) {
	    // Se 'hash' foi passada, atribui o valor passado a $hash
	    $hash = $_GET['hash'];
	} else {
	    // Se 'hash' não foi passada, tenta pegar o valor da sessao
	    if (isset($_SESSION['hash'])) {
	    $hash = $_SESSION['hash'];
	    } else { // se não tiver na sessão, atribui o valor 1 a $hash para que nao dê erro
		echo "<h2>Solicite uma nova chave de abertura das notas pelo email selecao0124@fundacentro.gov.br (hash).</h2>"; 
		exit;
	    }
	}

	if (isset($_GET['nome_usuario'])) {
	    // Se 'nome_usuario' foi passada, atribui o valor passado a $nome_usuario
	    $nome_usuario = $_GET['nome_usuario'];
	} else {
	    // Se 'nome_usuario' não foi passada, tenta pegar o valor da sessao
	    if (isset($_SESSION['nome_usuario'])) {
	    $nome_usuario = $_SESSION['nome_usuario'];
	    } else { // se não tiver na sessão, atribui o valor 1 a $nome_usuario para que nao dê erro
		echo "<h2>Solicite uma nova chave de abertura das notas pelo email selecao0124@fundacentro.gov.br (nome_usuario).</h2>"; 
		exit;
	    }
	}

	include 'identifica.php.cripto';
	
	$string_para_hash = str_replace('_',' ',$nome_usuario) . $contra_senha; // Concatenação da string
	
	$hash_verificacao = hash('sha256', $string_para_hash);
	
	if ($hash_verificacao == $hash) { $_SESSION['loggedin'] = true; $_SESSION['nome_usuario'] = $nome_usuario; $_SESSION['hash'] = $hash;}
	
	else {echo "<h2>Solicite uma nova chave de abertura das notas pelo email selecao0124@fundacentro.gov.br (mismatch).</h2>"; exit;}

	$atividade = "Inserção de evidência - Inseridência";

function inserirLog($conn, $nomeUsuario, $atividade) {
    // Prepara uma declaração SQL para buscar o id_pessoa baseado no nome do usuário
    $stmt = $conn->prepare("SELECT id_chave_pessoa FROM pessoas WHERE nome_pessoa = ?");
    $stmt->bind_param("s", $nomeUsuario);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Se o usuário for encontrado, obtém o id_pessoa
        $row = $result->fetch_assoc();
        $idPessoa = $row['id_chave_pessoa'];
    } else {
        // Se o usuário não for encontrado, ajusta idPessoa para null e modifica a atividade
        $idPessoa = NULL;
        $atividade = "Usuário não encontrado: " . $atividade;
    }

    // Prepara uma segunda declaração SQL para inserir o log
    $stmt = $conn->prepare("INSERT INTO logs (id_pessoa, atividade, nome_pessoa_ext) VALUES (?, ?, ?)");
    $stmt->bind_param("iss", $idPessoa, $atividade, $nomeUsuario);

    // Executa a declaração preparada
    if ($stmt->execute()) {
        echo "Usuário logado.";
    } else {
        echo "Erro ao inserir log: " . $stmt->error;
    }

    // Fecha a declaração
    $stmt->close();
}


$nome_usuario = str_replace('_',' ',$nome_usuario);


function buscarValorCampo($id_evidencia, $nomeCampo, $nomeTabela, $nomeCampoChave) {
    global $conn; // Usando a conexão como uma variável global

    $id_evidencia = $conn->real_escape_string($id_evidencia);
    $nomeCampo = $conn->real_escape_string($nomeCampo);
    $nomeTabela = $conn->real_escape_string($nomeTabela);

    $query = "SELECT ".$nomeCampo." FROM ".$nomeTabela." WHERE ".$nomeCampoChave." = '".$id_evidencia."' LIMIT 1";
    $resultado = $conn->query($query);

    if ($resultado && $resultado->num_rows > 0) {
        $row = $resultado->fetch_assoc();
        return $row[$nomeCampo];
    } else {
        return null; // Ou manipule o erro conforme necessário
    }
}

$id_evidencia = $_GET['id_evidencia'] ?? "nulo"; // se não for passado o id_evidencia, assume nulo

// Verifica se 'hash' foi passada na URL

echo "
<!DOCTYPE html>
<html  lang='pt-BR' >
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1'>
	<title>Inseridência $id_evidencia ($nome_usuario)</title>
	<link rel='stylesheet' href='https://unpkg.com/leaflet@1.7.1/dist/leaflet.css' />
	<style>


/* http://meyerweb.com/eric/tools/css/reset/ 
   v2.0 | 20110126
   License: none (public domain)
*/

html, body, div, span, applet, object, iframe,
h1, h2, h3, h4, h5, h6, p, blockquote, pre,
a, abbr, acronym, address, big, cite, code,
del, dfn, em, img, ins, kbd, q, s, samp,
small, strike, strong, sub, sup, tt, var,
b, u, i, center,
dl, dt, dd, ol, ul, li,
fieldset, form, label, legend,
table, caption, tbody, tfoot, thead, tr, th, td,
article, aside, canvas, details, embed, 
figure, figcaption, footer, header, hgroup, 
menu, nav, output, ruby, section, summary,
time, mark, audio, video {
	margin: 0;
	padding: 0;
	border: 0;
	font-size: 100%;
	font: inherit;
	vertical-align: baseline;
}
/* HTML5 display-role reset for older browsers */
article, aside, details, figcaption, figure, 
footer, header, hgroup, menu, nav, section {
	display: block;
}
body {
	line-height: 1;
}
ol, ul {
	list-style: none;
}
blockquote, q {
	quotes: none;
}
blockquote:before, blockquote:after,
q:before, q:after {
	content: '';
	content: none;
}
table {
	border-collapse: collapse;
	border-spacing: 0;
}

/*  * {
  outline: 1px solid #f00 !important;
}  isso aqui permite ver os limites dos elementos para debug*/

		html {
			background-color: black;
		}

		html, body {
			height: auto;
			min-height: 100%;
			width: auto;
			overflow-x: hidden!important;
			overflow-y: auto;
			overscroll-behavior: none;
		}	
		option {
			width: 200px;
			color: blue;
			background-color: white;
		}
		#cabecalio {
			max-height: 10%;
			display: flex;
			flex-direction: row;
			justify-content: space-between; /* Centraliza os elementos verticalmente */
			align-items: center;      /* Centraliza os elementos horizontalmente */
			position: absolute;
			width: 100%;
			top:0px;
			left: 0px;
			border: 1px solid gray;
			background-color: #103050;
			color: white;
			box-sizing: border-box;
		}
		#resto {
			width: 100%;
			position: absolute;
			justify-content: space-between; /* Centraliza os elementos verticalmente */
			background-color: gray;
			padding: 20px;
			border: 2px solid white;
			border-radius: 10px;
	    		box-sizing: border-box;	
		}
		.campo {
			display: flex;
 			flex-direction: column; /* Empilha os elementos verticalmente */
			flex-shrink: 1;
			max-width: 100%;
			min-width: 0;
			overflow: auto;	/* Garante que não haja rolagem horizontal no contêiner principal */
			align-items: flex-start; /* Isso garante que os itens estejam centralizados verticalmente */
			border: 1px solid black;
			background-color: orange;
			color: black;
			padding: 10px;
			margin: 5px auto;
			border-radius: 10px;
			box-sizing: border-box;
		}
		.campo > * {
			margin-top: 3px;
			margin-bottom: 3px;
		}
		.shrinkable {
			flex-shrink: 1;
			max-width: 100%;
			min-width: 0;
			overflow: auto;	/* Garante que não haja rolagem horizontal no contêiner principal */
			margin: 5px auto; 

		}
		.coluna {
		    display: flex;
		    flex-direction: column;
		    align-items: flex-start; /* Isso garante que os itens estejam centralizados verticalmente */
		}

		.linha {
		    display: flex;
		    flex-direction: row;
		    align-items: center; /* Isso garante que os itens estejam centralizados verticalmente */
		}
		.linha_left {
		    display: flex;
		    flex-direction: row;
		    align-items: flex-start; /* Isso garante que os itens estejam centralizados verticalmente */
		}
	
		.results {
		    overflow-y: auto;
		    position: absolute;
			z-index: 10;
			border: 1px solid black;
			background-color: white;
			font-size: 1.1em;
		}
		input[type='text'] {
			font-size:1.22rem;
			border-radius: 5px;
			max-width: 100%;
			width: 100%;
			overflow: auto;	/* Garante que não haja rolagem horizontal no contêiner principal */
			box-sizing: border-box;
		}
		
		input[type='text']:focus {
			border: 5px solid red;
		
		}
		.item {
		    padding: 8px;
		    cursor: pointer;
		}
		
		.item:hover {
		    background-color: #f7f7f7;
			color: black;
		}
		
		.selected {
		    background-color: #000000;
			color: #FFFFFF;
		}

		#div_form_upload {
			display: none;
			padding: 10px;
		    flex-direction: row; 
			flex-wrap: wrap; /* Quebra a linha quando não há mais espaço */
            justify-content: space-between;  /* Centraliza os elementos verticalmente */
            /*align-items: center;      Centraliza os elementos horizontalmente */
			border: 1px solid black;
			border-radius: 10px;
			background-color: gray;
			width: 100%;
			margin: auto;
		    box-sizing: border-box;
		}
@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}

/* Adicione isso ao seu arquivo CSS */
.loader {
    background-image: url('ampulheta_pequena.png'); /* Substitua pelo caminho da sua imagem */
    background-size: auto 100%;
    background-repeat: no-repeat;
    background-position: center;
    width: 79px; /* ou o tamanho desejado */
    height: 50px; /* ou o tamanho desejado */
    position: fixed; /* Fica no meio da tela */
    top: 50%;
    left: 50%;
    margin-top: -50px; /* Metade da altura */
    margin-left: -50px; /* Metade da largura */
    display: none; /* Inicialmente oculto */
    z-index: 10000; /* Sempre acima de todos */
    animation: spin 2s linear infinite; /* Aplica a animação */
}

#grava_status {
	font-size: 0.4rem;
	margin: 5px;
}

.nome_campo {
	font-weight: bold;
}

.imagem-container {
  flex: 1;
  display: flex;
  justify-content: flex-end;
  align-items: center;
}

.logo_cabecalio {
    margin: 2px;
    border: 2px solid black;
    border-radius: 5px;
}

.titulo_geral {
	font-weight: bold;
	font-size: 1.1rem;
	flex-shrink: 1;
	word-wrap: break-word; /* ou overflow-wrap: break-word; */	
}

#escolhe_autor {
    flex-grow: 1;
    display: none;
    flex-direction: column;
    justify-content: space-between; /* Centraliza os elementos verticalmente */
    align-items: center;
    padding: 20px;
    border: 1px solid #ccc;
    border-radius: 4px;
    margin: auto;
    overflow: hidden; /* Garante que não haja rolagem horizontal no contêiner principal */
    box-sizing: border-box;
}

.container_identificadores {
    flex-grow: 1;
    display: flex;
    flex-direction: column;
    padding: 20px;
    border: 1px solid #ccc;
    border-radius: 4px;
    margin: 5px;
    overflow: auto;
}

.container_identificadores > .dropdown-wrapper {
      display: flex;
      flex-shrink: 1;
      max-width: 100%;
      min-width: 0;
    flex-direction: column;
    align-items: flex-start;
    vertical-align: top; /* Alinha os divs pelo topo. */
    margin-right: 10px; /* Espaço entre os divs. Ajuste conforme necessário. */
        /* overflow: visible; */
        padding: 10px;
}
.container_identificadores > .results {
   max-height: 30%;
    overflow-y: auto;
        z-index: 10000;
        border: 1px solid black;
        background-color: white;
        font-size: 1.1em;

}

#escolhe_autor > #mostra_autores {
    flex-shrink: 1;
    background-color: white; 
    max-width: 100%;
    border: 1px solid black;
    border-radius: 5px;
    overflow-y: auto; /* Rolagem vertical */
    overflow-x: hidden; /* Garante que o conteúdo mais largo seja cortado e não cause rolagem horizontal */
    white-space: nowrap; /* Evita quebra de linha do conteúdo */
}

.item_autor{
	padding: 2px;
	overflow-y: auto;
}

		#up load_form {
		    display: flex;
		    flex-direction: row;
		    max-width: 300px;
		    padding: 20px;
		    border: 1px solid #ccc;
		    border-radius: 4px;
			margin:5px;
		}

		input[type='file'] {
		    margin-bottom: 10px;
		}
		#lente_container {
			flex-grow: 1;
			display: flex;
			flex-direction: column; /* Empilha os elementos verticalmente */
		     justify-content: space-between; /* Centraliza os elementos verticalmente */
		    align-items: center;     /* Centraliza os elementos horizontalmente */
			margin: 5px;
			padding: 10px;
			width: auto;
			border: 1px solid #ccc;
			border-radius: 4px;
		}
		.clearfix { /* limpa os float */
		    display: flex;
		    flex-wrap: wrap;
		}

		.imagem-texto {
            color: white;
            font-weight: bold;
            background-color: rgba(0, 0, 0, 0.5);
            padding: 10px;
            border-radius: 5px;
            text-align: center;
            z-index: 1;
        }
	
		#lente_busca {
		    display: block;
			cursor: pointer;
			margin: 10px;
			padding: 10px;
			background-color: yellow;
			border:1px solid black;
			border-radius: 10px;
		   	max-width: 100%;
    		max-height: 100%;
		max-width: 100%;
		margin: auto;
		box-sizing: border-box;
		    border: 2px solid transparent; /* Adiciona uma borda transparente para manter o tamanho consistente */
		    transition: transform 0.1s; /* Suaviza a animação de pressionar o botão */
		}

		#lente_busca:hover {
		   border: 3px solid #333; /* Muda a cor da borda ao passar o mouse para dar feedback */
		}

		#lente_busca:active {
		    transform: scale(0.95); /* Pressiona a imagem quando clicada, dando a sensação de um botão sendo pressionado */
		}

		#fileToUpload {
			display: none;
		}
		#botao_upload {
			display: none;
		}

#container_mapa {


}

#mapa {
            width: 30vw; /* Largura flexível */
            height: 30vw; /* Altura fixa */
	    flex-basis: 30%;
	    z-index: 1000000;
	    visibility: visible;
        }

@media screen and (max-width: 600px) {

#mapa {
            width: 100%; /* Largura flexível */
            height: 100vw; /* Altura fixa */
	    flex-basis: 100%;
	    z-index: 1000000;
	    visibility: visible;
        }


.loader {
    /* outras propriedades */
    top: 25vh; /* 50% da altura do viewport */
    left: 50vw; /* 50% da largura do viewport */
    transform: translate(-50%, -25%);
}

}
	</style>
<script src='https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.11.338/pdf.min.js'></script>
pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.11.338/pdf.worker.min.js';


	<script>

var marcadorDestaque;

var min_top=1000000; // valor inicial para o top, que indicará o elemento mais acima na tela para o debugger_definitivo.php
var max_bottom=-1000000; // valor inicial para o bottom, que indicará o elemento mais abaixo na tela para o debugger_definitivo.php
var min_left=1000000; // valor inicial para o left, que indicará o elemento mais à esquerda na tela para o debugger_definitivo.php
var max_right=-1000000; // valor inicial para o right, que indicará o elemento mais à direita na tela para o debugger_definitivo.php

function validarInputInline(input, tvrregexp, mensagemErro) {
    if (typeof tvrregexp === 'string') {
        // Assume-se que a expressão regular está entre delimitadores '/' e pode conter flags
        // Exemplo: '/expressao/i'
        const parts = tvrregexp.match(/^\/(.*?)\/([gimyu]*)$/);
        if (parts) {
            tvrregexp = new RegExp(parts[1], parts[2]);
	    console.log('Expressão regular válida:', tvrregexp);
        } else {
	    console.log('Expressão regular inválida:', tvrregexp);
            console.error('Expressão regular inválida:', tvrregexp);
            return; // Saia da função se a regex não for válida
        }
    }
    console.log(tvrregexp.test(input.value));
    console.log(tvrregexp);
    console.log(input.value);
    let hintElement = input.previousSibling;
    //let hintElement = input.nextSibling;
    if (!tvrregexp.test(input.value) && input.value.length>0) {
        if (!hintElement ||  hintElement.className !== 'input-hint') {
	    hintElement = document.createElement('div');
            hintElement.className = 'input-hint';
            hintElement.style.color = 'red';
            hintElement.style.border = '1px solid red';
	    hintElement.style.borderRadius = '5px';
            hintElement.style.backgroundColor = 'white';
            hintElement.style.position = 'absolute';
            hintElement.style.marginBottom = '5px';
            hintElement.style.padding = '5px';
            hintElement.textContent = mensagemErro;

            // Insere o hintElement antes do input
            input.parentNode.insertBefore(hintElement, input);
        }
    } else {
        if (hintElement && hintElement.className === 'input-hint') {
            hintElement.remove();
        }
    }
}


//function validarInputInline(input, tvrregexp, mensagemErro) {
//    //console.log(tvrregexp.test(input.value));
//    let hintElement = input.nextSibling;
//    if (!tvrregexp.test(input.value)) {
//        if (!hintElement || hintElement.nodeName !== 'SPAN') {
//            hintElement = document.createElement('span');
//            hintElement.style.color = 'red';
//            hintElement.textContent = mensagemErro;
//            input.parentNode.insertBefore(hintElement, input.nextSibling);
//        }
//    } else {
//        if (hintElement && hintElement.nodeName === 'SPAN') {
//            hintElement.remove();
//        }
//    }
//}

function validarInput(idInput, regexp, mensagemErro) {
    const input = document.getElementById(idInput);
    const hintElement = document.createElement('span');
    hintElement.style.color = 'red';

    input.addEventListener('input', function() {
        if (!regexp.test(input.value)) {
            if (!input.nextSibling) { // Se não houver um hint, adicione um
                hintElement.textContent = mensagemErro;
                input.parentNode.insertBefore(hintElement, input.nextSibling);
            }
        } else {
            if (input.nextSibling) { // Se o valor for válido, remova o hint
                input.nextSibling.remove();
            }
        }
    });
}


function verificaBoundingBox_id_evidencia(latitude, longitude, id_evidencia) {
    const dados = { latitude, longitude, id_evidencia };

    fetch('verificaBoundingBox.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(dados)
    })
    .then(response => response.json())
    .then(data => {
        if (data.error) {
            console.error(data.error);
        } else {
            console.log('Resultado:', data);
        }
    })
    .catch(error => console.error('Erro na requisição:', error));
}

function verificaBoundingBox_id_evidencia_promise(latitude, longitude, id_evidencia) {
//alert('latitude: '+latitude+' longitude: '+longitude+' id_evidencia: '+id_evidencia);
    return new Promise((resolve, reject) => {
        const dados = { latitude, longitude, id_evidencia };

        fetch('verificaBoundingBox.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(dados)
        })
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                reject(data.error); // Rejeita a Promise se houver um erro
            } else {
                resolve(data); // Resolve a Promise com os dados
            }
        })
        .catch(error => reject(error)); // Rejeita a Promise se houver um erro na requisição
    });
}




function encontrarEndereco(lat, lon, id_evidencia) {
    return new Promise((resolve, reject) => {
        var url = 'https://nominatim.openstreetmap.org/reverse?format=json&lat=' + lat + '&lon=' + lon;

        fetch(url)
            .then(response => {
                if (!response.ok) {
                    throw new Error('HTTP error! status: ' + response.status);
                }
                return response.json();
            })
            .then(data => {
                resolve(data);
                enviarDadosParaServidorEndereco(data, id_evidencia); // Enviar dados para o servidor
            })
            .catch(error => {
                reject(error);
            });
    });
}

function enviarDadosParaServidorEndereco(data, id_evidencia) {
    console.log(data);
    data.id_evidencia = id_evidencia;
    fetch('grava_enderecos_evidencias.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(data => console.log(data))
    .catch(error => console.error('Error:', error));
}




function calcularTamanhoDoIcone(zoom) {
    // Defina o tamanho mínimo e máximo para os ícones
    const tamanhoMinimo = 20;
    const tamanhoMaximo = 50;

    // Defina o nível de zoom mínimo e máximo para os quais a função é projetada
    const zoomMinimo = 0;
    const zoomMaximo = 18;

    // Calcule um fator de escala baseado no nível de zoom atual
    const fatorEscala = (zoom - zoomMinimo) / (zoomMaximo - zoomMinimo);

    // Calcule o tamanho atual baseado no fator de escala
    const tamanhoAtual = tamanhoMinimo + fatorEscala * (tamanhoMaximo - tamanhoMinimo);

    // Garanta que o tamanho não exceda os limites definidos
    const tamanhoFinal = Math.max(tamanhoMinimo, Math.min(tamanhoAtual, tamanhoMaximo));

    return [tamanhoFinal, tamanhoFinal]; // Retorna o tamanho como uma tupla [largura, altura]
}


function isTouchDevice() {
    // Verifica se o dispositivo suporta eventos de toque
    return ('ontouchstart' in window)        // Funciona na maioria dos navegadores
        || (navigator.maxTouchPoints > 0)    // Pointer Events API
        || (navigator.msMaxTouchPoints > 0); // Para navegadores antigos com prefixo ms
}

 // Função para ajustar a altura do body conforme necessário
        function ajustarAlturaDoBody(divFilha) {
            const body = document.body;

            // Obtém a altura da div filha
            const alturaDaDivFilha = divFilha.clientHeight;

            // Define a altura do body para a altura da div filha
            body.style.height = alturaDaDivFilha + 'px';
        }

function carregarMapa(idEvidencia) {
    var mapa = L.map('mapa').setView([-23.62830000, -46.64090000], 2); // Coordenadas iniciais



    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '© OpenStreetMap contributors'
    }).addTo(mapa);
   
    var localizacaoBotao = L.control({position: 'topright'});
    localizacaoBotao.onAdd = function (mapa) {
        var div = L.DomUtil.create('div', 'botao-localizacao');
        div.innerHTML = '<button onclick=\"irParaLocalizacaoAtual()\">📍</button>';
        return div;
    };
    localizacaoBotao.addTo(mapa);

window.irParaLocalizacaoAtual = function() {
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(function(position) {
                var latlng = new L.LatLng(position.coords.latitude, position.coords.longitude);
                mapa.setView(latlng, 15); // Define o zoom para 15, mas pode ser ajustado
            });
        } else {
            alert('Geolocalização não é suportada por este navegador.');
        }
    };
 
    fetch('buscarCoordenadas.php')
        .then(response => response.json())
        .then(data => {
            let markers = [];
            data.forEach(coord => {
                var icone = L.icon({
                    iconUrl: coord.id_chave_evidencia === idEvidencia ? 'fundacentro_transp_azul.png' : 'fundacentro_transp.png',
		    zIndexOffset: coord.id_chave_evidencia === idEvidencia ? 10000000 : 0,
                    iconSize: [38, 38],
                    iconAnchor: [22, 94],
                    popupAnchor: [-3, -76]
                });

	        var marker = L.marker([coord.latitude, coord.longitude], {icon: icone});
		if (coord.id_chave_evidencia === idEvidencia) {
			marcadorDestaque = marker;
		}
                markers.push(marker); // Adiciona cada marcador ao array
                marker.addTo(mapa);

            });

            // Ajustar o zoom para mostrar todos os marcadores
            mapa.fitBounds(L.featureGroup(markers).getBounds());

		if (!isTouchDevice) {
	        	// Adiciona controle de zoom com tecla pressionada somente para dispositivos não touch
		        var zoomEnabled = false;
	        	document.addEventListener('keydown', function(e) {
		        	if (e.key === 'Control' && !zoomEnabled) { // Substitua 'Control' pela tecla desejada
	        	        	mapa.scrollWheelZoom.enable();
	                		zoomEnabled = true;
	            		}
	        	});
		        document.addEventListener('keyup', function(e) {
	        	    	if (e.key === 'Control' && zoomEnabled) {
	                		mapa.scrollWheelZoom.disable();
					zoomEnabled = false;
	            		}
	        	});
		} // if
			//alert('vai atualizar altura');
			atualizarAlturaDoBody('div_form_upload');
		mapa.on('zoomend', function() {
		    var currentZoom = mapa.getZoom();
		    markers.forEach(marker => {
		        var newSize = calcularTamanhoDoIcone(currentZoom); // Função fictícia para calcular o tamanho
		        var novoIcone = L.icon({
		            iconUrl: marker.getIcon().options.iconUrl,
		            iconSize: newSize,
		            iconAnchor: [newSize[0] / 2, newSize[1] / 2],
		            popupAnchor: [-3, -76]
		        });
		        marker.setIcon(novoIcone);
		    });
			 if (marcadorDestaque) {
			        // Ajustar o zIndexOffset
			        marcadorDestaque.setZIndexOffset(1000);
			    }
		});

        });
} // fim carregarMapa


function ajustarAlturaDoBody_2(divFilha) {
    const body = document.body;
    const resto = document.getElementById('resto'); // Div que contém o conteúdo dinâmico
    const container0 = document.getElementById('form_insere_evidencia'); // Div que contém o conteúdo dinâmico
    const container = document.getElementById(divFilha); // Div que contém o conteúdo dinâmico

    // Obtém a altura do conteúdo dinâmico no container
    const alturaDoConteudoDinamico = container.getBoundingClientRect().height + container0.getBoundingClientRect().height + container0.getBoundingClientRect().top + window.scrollY;
	// alert(alturaDoConteudoDinamico + ' 1 ' + window.innerHeight + ' / ' + resto.getBoundingClientRect().height + divFilha);
    // Calcula a altura máxima entre a altura do viewport e a altura do conteúdo dinâmico
    const alturaMaxima = Math.max(window.innerHeight, alturaDoConteudoDinamico);
    // Define a altura do body para a altura máxima
    body.style.height = alturaMaxima + 'px';
    resto.style.height = alturaMaxima + 'px';
	//alert(alturaDoConteudoDinamico + ' 2 ' + window.innerHeight);
}


function atualizarAlturaDoBody(divFilha) {
            setTimeout(function () {ajustarAlturaDoBody_2(divFilha);}, 500);
        }


function enviarDadosParaServidor(dados) {
    console.log(dados);
    var xhr = new XMLHttpRequest();
    xhr.open('POST', 'debugger_definitivo.php', true);
    xhr.setRequestHeader('Content-Type', 'application/json');
    xhr.send(JSON.stringify(dados));
}

function coletarDadosDoElemento(elemento) {
    var estilosComputados = window.getComputedStyle(elemento);
    var retangulo = elemento.getBoundingClientRect();
    let elementoParentNodeTag;
    let elementoParentNodeId;	
    let elementoParentNodeClass;
    let elementoParentElementTag;
    let elementoParentElementId;
    let elementoParentElementClass;


    if (elemento.parentNode === null) {
	elementoParentNodeTag = 'ParentNode nulo';
	elementoParentNodeId = 'ParentNode nulo';
	elementoParentNodeClass = 'ParentNode nulo';
    } else {
	elementoParentNodeTag = elemento.parentNode.tagName;
	elementoParentNodeId = elemento.parentNode.id;
	elementoParentNodeClass = elemento.parentNode.className;
    }

    if (elemento.parentElement === null) {
	elementoParentElementTag = 'ParentElement nulo';
	elementoParentElementId = 'ParentElement nulo';
	elementoParentElementClass = 'ParentElement nulo';
    } else {
	elementoParentElementTag = elemento.parentElement.tagName;
	elementoParentElementId = elemento.parentElement.id;
	elementoParentElementClass = elemento.parentElement.className;
    }

	if ( retangulo.top < min_top) {min_top=retangulo.top;}
	if ( retangulo.bottom > max_bottom) {max_bottom=retangulo.bottom;}
	if ( retangulo.left < min_left) {min_left=retangulo.left;}
	if ( retangulo.right > max_right) {max_right=retangulo.right;}

    return {
        tag: elemento.tagName,
        id: elemento.id,
	type: elemento.type,
        class: elemento.className,
	tag_no_pai: elementoParentNodeTag,
	id_no_pai: elementoParentNodeId, 
	class_no_pai: elementoParentNodeClass, 
	tag_elemento_pai: elementoParentElementTag,
	id_elemento_pai: elementoParentElementId, 
	class_elemento_pai: elementoParentElementClass, 
        position: estilosComputados.position,
        display: estilosComputados.display,
        top: retangulo.top,
        left: retangulo.left,
        bottom: retangulo.bottom,
        right: retangulo.right,
        height: retangulo.height,
        width: retangulo.width,
        padding: estilosComputados.padding,
        margin: estilosComputados.margin,
	flex_shrink: estilosComputados.flexShrink,
	max_width: estilosComputados.maxWidth,
	min_width: estilosComputados.minWidth,
	overflow: estilosComputados.overflow
    };
}

function percorrerElementosDoDOM() {
    alert('vai percorrer');
    var todosElementos = document.querySelectorAll('*');
    var dadosElementos = Array.from(todosElementos).map(coletarDadosDoElemento);
    var dados = {
	min_top: min_top,
	max_bottom: max_bottom,
	min_left: min_left,
	max_right: max_right,
    };
    dadosElementos.unshift(dados);
    //enviarDadosParaServidor(dadosElementos);
}





function apagarAutor(id_chave_autor_evidencia, id_evidencia) {
	alert('vai apagar :'+id_chave_autor_evidencia+ ' / '+id_evidencia);
    var url = 'apagarAutor.php?id_chave_autor_evidencia=' + id_chave_autor_evidencia;
    fetch(url)
        .then(function(response) {
            return response.json();
        })
        .then(function(data) {
            if (data.success) {
                alert('Registro deletado com sucesso!');
            } else {
                alert('Erro ao deletar o registro.');
            }
        })
        .catch(function(error) {
            console.error('Houve um erro ao fazer a requisição:', error);
        });

getAutoresByEvidencia(id_evidencia).then(htmlContent => {
    document.getElementById(`mostra_autores`).innerHTML = htmlContent;
});



}

// a funcao abaixo está obsoleta. quando o código estiver estabilizado, pode ser apagada
function grava_e_mostra_os_autores(){

let input_autores =  document.getElementById(`input_autores`);
let id_pessoa = input_autores.getAttribute(`data-id-token`); 
let id_evidencia = input_autores.getAttribute(`data-id-evidencia`);

if (id_pessoa.length>0) 
	{inserirAutorEvidencia(id_pessoa, id_evidencia);} 
else 
	{alert(`Escolha um autor usando a seta para baixo!`)}; 
	
getAutoresByEvidencia(id_evidencia).then(htmlContent => {
    document.getElementById(`mostra_autores`).innerHTML = htmlContent;	
});

input_autores.value = '';
input_autores.setAttribute('data-id-token','');
input_autores.setAttribute('data-selecionou','nao');

}


async function grava_e_mostra_os_autores_async() {
    let input_autores = document.getElementById(`input_autores`);
    let id_pessoa = input_autores.getAttribute(`data-id-token`);
    let id_evidencia = input_autores.getAttribute(`data-id-evidencia`);

    if (id_pessoa.length > 0) {
        await inserirAutorEvidencia_async(id_pessoa, id_evidencia);
    } else {
        alert(`Escolha um autor usando a seta para baixo!`);
    }

    // Aguarda a resposta da função getAutoresByEvidencia
    const htmlContent = await getAutoresByEvidencia(id_evidencia);

    document.getElementById(`mostra_autores`).innerHTML = htmlContent;

    input_autores.value = '';
    input_autores.setAttribute('data-id-token', '');
    input_autores.setAttribute('data-selecionou', 'nao');
}

// Mantenha a função getAutoresByEvidencia como está


async function getAutoresByEvidencia(id_evidencia_param) {
    const response = await fetch('buscarAutoresPorEvidencia.php?id_evidencia=' + id_evidencia_param);
    const data = await response.json();

    let htmlContent = '';

    data.forEach(autor => {
        htmlContent += '<div title=\"'+autor.nome_pessoa+'\"  id=\"item_autor_'+autor.id_chave_autor_evidencia+'\" class=\"item_autor\" >' +
            '<button data-id-autores-evidencias=\"' + autor.id_chave_autor_evidencia + '\" onclick=\"alert('+id_evidencia_param+'); apagarAutor(' + autor.id_chave_autor_evidencia + ','+ id_evidencia_param+')\">Apaga</button>&nbsp;' +
            autor.nome_pessoa +
            '</div>';
    });

    return htmlContent;
}

async function inserirAutorEvidencia_async(id_pessoa, id_evidencia) {
    const data = {
        id_pessoa: id_pessoa,
        id_evidencia: id_evidencia
    };

    try {
        const response = await fetch('inserir_autor_evidencia.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(data)
        });

        const responseData = await response.json();

        if (responseData.success) {
            console.log('Registro inserido com sucesso!');
            return true;
        } else {
            console.error('Erro ao inserir registro:', responseData.error);
            return false;
        }
    } catch (error) {
        console.error('Erro na requisição:', error);
        return false;
    }
} // fim inserirAutorEvidencia


// a funcao abaixo está obsoleta. quando o código estiver estabilizado, pode ser apagada
function inserirAutorEvidencia(id_pessoa, id_evidencia) {
    const data = {
        id_pessoa: id_pessoa,
        id_evidencia: id_evidencia
    };

    fetch('inserir_autor_evidencia.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            console.log('Registro inserido com sucesso!');
        } else {
            console.error('Erro ao inserir registro:', data.error);
        }
    })
    .catch(error => {
        console.error('Erro na requisição:', error);
    });
} // fim inserirAutorEvidencia


	document.addEventListener('DOMContentLoaded', 
	function() {
	//alert('DOMload');
		  var windowHeight = window.innerHeight || document.documentElement.clientHeight;
		  var upperDivHeight = document.querySelector('#cabecalio').offsetHeight;
		  var dynamicDiv = document.getElementById('resto');
		  dynamicDiv.style.height = (windowHeight - upperDivHeight) + 'px';
		  dynamicDiv.style.top = upperDivHeight + 'px';
		  dynamicDiv.style.left = '0px';
		var urlAtual = window.location.href;
		// Remove todos os parâmetros após o ponto de interrogação
		var urlSemParametros = urlAtual.replace(/\?.*$/, '');
		// Atualiza a URL para a versão sem parâmetros
		window.history.replaceState(null, null, urlSemParametros);



function carrega_event_upload_pdf() {
    var uploadForm = document.getElementById('upload_form'); 
    uploadForm.addEventListener('submit', function(e) {
        e.preventDefault();

        let formaData2 = new FormData(uploadForm);
        var inputFile = document.getElementById('fileToUpload');
        var file = inputFile.files[0];

        // Verifica se o arquivo é um PDF
        if (file.type === 'application/pdf') {
            // Interrompe o envio do formulário
            e.preventDefault();

            // Inicia o processo de conversão de PDF para JPG
            var fileReader = new FileReader();

            fileReader.onload = function() {
                var typedarray = new Uint8Array(this.result);

                pdfjsLib.getDocument({data: typedarray}).promise.then(function(pdf) {
                    // Obtém a primeira página
                    pdf.getPage(1).then(function(page) {
                        var viewport = page.getViewport({scale: 1.5});
                        var canvas = document.createElement('canvas');
                        var context = canvas.getContext('2d');
                        canvas.height = viewport.height;
                        canvas.width = viewport.width;

                        // Renderiza a página do PDF no canvas
                        var renderContext = {
                            canvasContext: context,
                            viewport: viewport
                        };
                        page.render(renderContext).promise.then(function() {
                            // Converte o canvas para uma imagem JPG e adiciona ao FormData
                            canvas.toBlob(function(blob) {
                                formaData2.set('fileToUpload', blob, 'converted.jpg');
                                
                                // Faz o upload da imagem convertida
                                fetchUpload(formaData2);
                            }, 'image/jpeg');
                        });
                    });
                });
            };

            fileReader.readAsArrayBuffer(file);
        } else {
            // Para arquivos não-PDF, continua com o upload normal
            fetchUpload(formaData2);
        }
    });

    document.getElementById('fileToUpload').addEventListener('change', function(event) {
        let loadingIndicator = document.getElementById('loadingIndicator');                                    
	loadingIndicator.style.display = 'block'; // mostrar o indicador de carregamento 

        var inputFile_2 = event.target;
        event.preventDefault();
        if (inputFile_2.files.length > 0) {
            document.getElementById('botao_upload').click();
        }
    });
}

function fetchUpload(formData) {
    fetch('upload_arquivo_hash.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.mostra_mensagem) { alert(data.message); }
        let loadingIndicator = document.getElementById('loadingIndicator');                                    
	loadingIndicator.style.display = 'none'; // Ocultar o indicador após carregar os dados      
        document.getElementById('lente_busca').src = '../../imagens/' + data.arquivo;
    })
    .catch(error => {
        console.error('Error:', error);
    });
}



function carrega_event_upload() {
		var uploadForm = document.getElementById('upload_form'); 
	    uploadForm.addEventListener('submit', function(e) {
	        e.preventDefault();
	
	        let formaData2 = new FormData(uploadForm);
	
	        fetch('upload_arquivo_hash.php', {
	            method: 'POST',
	            body: formaData2
	        })
	        .then(response => response.json()
		)
	        .then(data => {
				if (data.mostra_mensagem) {alert(data.message)};
	            document.getElementById('lente_busca').src = '../../imagens/'+data.arquivo;
	        })
	        .catch(error => {
	            console.error('Error:', error);
				//alert(error);
	        });
	    });
		document.getElementById('fileToUpload').addEventListener('change', function(event) {

		   var inputFile_2 = event.target;
				event.preventDefault();
		    if (inputFile_2.files.length > 0) {
				document.getElementById('botao_upload').click();
		    } else {
		    }

		});
} // function carrega_event_upload


function executa_script_forca_bruta(receivedHtml) {

		const div = document.createElement('div');
		div.innerHTML = receivedHtml;
		
		// Inserir o conteúdo no DOM (sem o script)
		// document.body.appendChild(div);
		
		// Encontrar e executar scripts manualmente
		const scripts = div.querySelectorAll('script');
		scripts.forEach((script) => {
		    const newScript = document.createElement('script');
		    newScript.innerHTML = script.textContent;
		    document.body.appendChild(newScript);
		    newScript.remove(); // remover o script após execução, se desejado
		});

} // fim function 

function disabilita_inputs(form, disable){

// var form = document.getElementById('meuFormulario');
var inputs = form.getElementsByTagName('input');

for (var i = 0; i < inputs.length; i++) {
    inputs[i].disabled = disable;
}
	

} // fim function

function consultaImagemUpload(idEvidencia) {
    // Cria um objeto XMLHttpRequest
    var xhr = new XMLHttpRequest();
	let loadingIndicator = document.getElementById('loadingIndicator');
	loadingIndicator.style.display = 'block'; // Mostrar o indicador de carregamento

    // Configura a requisição GET
    xhr.open('GET', 'mostra_imagem-upload.php?id_evidencia=' + idEvidencia, true);
	

    // Define o que deve acontecer quando a resposta chegar
    xhr.onload = function () {
        if (xhr.status >= 200 && xhr.status < 300) {
            // Processa a resposta
            var data = xhr.responseText;
            // Insere 'data' no elemento com id 'div_form_upload'
            document.getElementById('div_form_upload').insertAdjacentHTML('beforeend', data);
	    setTimeout(function () {carrega_event_upload_pdf();}, 100);

            // Chama a função 'executa_script_forca_bruta' com 'data' como parâmetro
            executa_script_forca_bruta(data);
	    disabilita_inputs(document.getElementById('form_insere_evidencia'), true); // true desabilita a entrada de dados
	    busque_identificadores(document.getElementById('last_inserted_id').value);
            loadingIndicator.style.display = 'none'; // Ocultar o indicador após carregar os dados      

        } else {
            // Manipula erros da requisição
            console.error('A requisição falhou: ', xhr.statusText);
        }
    };

    // Envia a requisição
    xhr.send();
} // fim consultaImagemUpload


function busque_identificadores(id) {
    // Construa a URL com o parâmetro id_evidencia
    const url = 'insere_indicadores_para_uma_evidencia_required.php?id_evidencia=' + id;

    // Faça a chamada GET
    fetch(url)
    .then(response => {
        // Verifique se a resposta está ok
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        // Converta a resposta para texto, já que estamos esperando HTML
        return response.text();
    })
    .then(html => {
        // Encontre o div onde queremos inserir o HTML
        const div = document.getElementById('div_form_upload');
        // Insira o HTML recebido
        div.insertAdjacentHTML('beforeend', html + '<div id=\"mapa\"></div>');
	carregarMapa(id);
	//atualizarAlturaDoBody('div_form_upload');
    })
    .catch(error => {
        console.error('There has been a problem with your fetch operation:', error);
    });
}

if ('".$id_evidencia."'!='nulo') {consultaImagemUpload('".$id_evidencia."');}

document.getElementById('form_insere_evidencia').addEventListener('submit', function(event) {
    event.preventDefault(); // Impede o envio tradicional do formulário
    let loadingIndicator = document.getElementById('loadingIndicator');                                    
    loadingIndicator.style.display = 'block'; // mostrar o indicador de carregamento 

    const enviaFormulario = (lat, lon) => {
        document.getElementById('latitude').value = lat;
        document.getElementById('longitude').value = lon;

        var formData = new FormData(this);

        fetch(this.action, {
            method: 'POST',
            body: formData
        })
        .then(response => response.text())
        .then(data => {
	    document.getElementById('div_form_upload').insertAdjacentHTML('beforeend', data); // Os scripts no receivedHtml SERÃO executados
	    executa_script_forca_bruta(data);	
	    //setTimeout(function (){carrega_event_upload();},1000);
	    carrega_event_upload_pdf();
	    disabilita_inputs(this, true); // true desabilita a entrada de dados
	    let id_evidencia = document.getElementById('last_inserted_id').value;
	    busque_identificadores(id_evidencia);
            // Resto do código de manipulação de resposta
            loadingIndicator.style.display = 'none';
	    // encontrarEndereco(lat, lon, id_evidencia);
		verificaBoundingBox_id_evidencia_promise(lat, lon, id_evidencia)
		    .then(data => {
		        // Verificar se o lat/long está dentro do bounding box gravado no banco
		        if (data.isInsideBoundingBox) {
		            //alert('Latitude e longitude já estão no banco de dados.');
		            // Fazer algo com os dados ou terminar aqui
		        } else {
		            // Se não estiver no bounding box, chamar encontrarEndereco
			    //alert('Latitude e longitude não estão no banco de dados: será preciso buscar no nominatim.');
		            return encontrarEndereco(lat, lon, id_evidencia);
		        }
		    })
		    .then(enderecoData => {
		        if (enderecoData) {
		            //alert('Endereço obtido:', enderecoData);
		            // Fazer algo com os dados do endereço
		        }
		    })
		    .catch(error => {
		        alert('Erro subindo:', error);
		        // Tratar erros aqui
		    });

        })
        .catch(error => {
            console.error('Erro ao enviar o formulário:', error);
            loadingIndicator.style.display = 'none';
        });
    };

    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(function(position) {
            var lat = position.coords.latitude;
            var lon = position.coords.longitude;
            enviaFormulario(lat, lon);
        });
    } else {
            var lat = 0;
            var lon = 0;
            enviaFormulario(lat, lon);
    }
});


//		document.getElementById('form_insere_evidencia').addEventListener('submit', function(event) {
//		//alert('vai inserir');
//		        let loadingIndicator = document.getElementById('loadingIndicator');                                    
//			loadingIndicator.style.display = 'block'; // mostrar o indicador de carregamento 
//
//			if (navigator.geolocation) {
//			    navigator.geolocation.getCurrentPosition(function(position) {
//			        var lat = position.coords.latitude;
//			        var lon = position.coords.longitude;
//				document.getElementById('latitude').value = lat;
//				document.getElementById('longitude').value = lon;
//			});
//			}
//
//		    event.preventDefault(); // Impede o envio tradicional do formulário
//		
//		    var formData = new FormData(this);
//		
//		    // Faz a requisição AJAX para o servidor
//		    fetch(this.action, {
//		        method: 'POST',
//		        body: formData
//		    })
//		    .then(response => response.text())
//		    .then(data => {
//		        // Exibe a resposta do servidor no elemento com id 'response'
//		        // document.getElementById('div_form_upload').innerHTML = data;
//				document.getElementById('div_form_upload').insertAdjacentHTML('beforeend', data); // Os scripts no receivedHtml SERÃO executados
//				executa_script_forca_bruta(data);	
//				//setTimeout(function (){carrega_event_upload();},1000);
//				carrega_event_upload_pdf();
//				disabilita_inputs(this, true); // true desabilita a entrada de dados
//				busque_identificadores(document.getElementById('last_inserted_id').value);
//				loadingIndicator.style.display = 'none'; // mostrar o indicador de carregamento 
//		    })
//		    .catch(error => {
//		        console.error('Erro ao enviar o formulário:', error);
//		    });
//		});
	});
	
	</script>
</head>
<body>
<div id='loadingIndicator' class='loader'></div>

<div id='cabecalio'>
	<div class='titulo_geral'>$nome_usuario, registre sua contribuição (".(($id_evidencia == "nulo")? "vazio":$id_evidencia).")</div>
	<input id='grava_status' type='button' value='grava status do dom' onclick='percorrerElementosDoDOM()'>
		<img class='logo_cabecalio' height='60vh' src='logo_fundacentro.jpeg'>
</div>
<div id='resto'>
";

include "identifica.php.cripto";

$servername = "localhost";
$password = $pass;
$dbname = $nome_base_dados; 

// Nome da tabela da qual você deseja gerar o formulário
$nomeTabela = "evidencias";



// Mapeamento dos tipos de coluna MySQL para tipos de entrada HTML
$tiposMapeados = [
    "varchar" => "text",
    "int" => "number",
    "date" => "date"
];

// Conexão com o banco de dados
$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Conexão falhou: " . $conn->connect_error);
}


inserirLog($conn, $nome_usuario, $atividade);


// Consulta para obter informações sobre a estrutura da tabela
$sql = "DESCRIBE $nomeTabela";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    echo "<form id='form_insere_evidencia' class='clearfix' action='processa_inseridencia.php' method='post'>";
    
    while ($row = $result->fetch_assoc()) {
        $nomeCampo = $row["Field"];
        $tipoCampo = $row["Type"];
        $chavePrimaria = $row["Key"] == "PRI"; // Verifica se é chave primária
        
        // Verifica se o tipo do campo é suportado e não é chave primária
        if (!$chavePrimaria) {
	    $valor_campo = buscarValorCampo($id_evidencia, $nomeCampo, $nomeTabela, $nome_chave_primaria) ?? "";
            foreach ($tiposMapeados as $tipo => $inputType) {
                if (strpos($tipoCampo, $tipo) === 0) {
                    // Verifica se é uma chave estrangeira
                    if (($nomeCampo === "id_token_tipo_de_evidencia") || ($nomeCampo === "id_token_tipo_de_veiculo") || ($nomeCampo === "id_pessoa")) {
		    // se nomeCampo for id_pessoa nao faz nada
			// Se for uma chave estrangeira, crie um dropdown
			$valor_campo_externo = buscarValorCampo($valor_campo, "nome_token", "tokens", "id_chave_token") ?? "";
                        // Se for uma chave estrangeira, crie um dropdown
if ($nomeCampo === "id_token_tipo_de_evidencia") 
{
echo "
	<div id='drop_tipo_de_evidencia' class='dropdown-wrapper campo' style='flex-grow: 1' data-sql='SELECT nome_tipo_de_evidencia, id_token FROM tipos_de_evidencias WHERE nome_tipo_de_evidencia LIKE ?;' data-companion-id='id_token_tipo_de_evidencia' data-input-companion='input_tipo_de_evidencia'>
		<label for='input_tipo_de_evidencia' class='nome_campo'>O que você fez ou participou?</label>  <!-- estava for=$nomeCampo com aspas simples -->
		<input id='input_tipo_de_evidencia' type='text' class='search-input drop_imprescindivel' autocomplete='off' name='".$nomeCampo."' placeholder='Digite para buscar...' data-tabela='$nomeTabela' data-campo='$nomeCampo' data-tabela-externa='tipos_de_evidencias' data-campo-nome-externo='nome_tipo_de_evidencias' data-id-externo='id_token' data-companion-id='id_token_tipo_de_evidencia'  data-companion-results='results_evidencias' data-id-botao='botao_de_envia'  value='$valor_campo_externo' data-escravo='drop_tipo_de_veiculo' >
  <input id='id_token_tipo_de_evidencia' type='hidden' name='id_token_tipo_de_evidencia' value='$valor_campo'>
	</div>
";
} 
if ($nomeCampo === "id_token_tipo_de_veiculo") 
{
echo "
	<div id='drop_tipo_de_veiculo' class='dropdown-wrapper campo' style='flex-grow: 1' data-sql='select \"\" as id_token, \"Selecione a evidência antes de selecionar o veículo\" as nome_tipo_de_evidencia  from tokens where nome_token like ? limit 1;'  data-mestre='drop_tipo_de_evidencia'  data-companion-id='id_token_tipo_de_veiculo' data-input-companion='input_tipo_de_veiculo'>
		<label for='input_tipo_de_veiculo' class='nome_campo'>Como você fez ou participou?</label>
		<input id='input_tipo_de_veiculo' type='text' class='search-input drop_imprescindivel' autocomplete='off' name='".$nomeCampo."' placeholder='Digite para buscar...' data-tabela='$nomeTabela' data-campo='$nomeCampo' data-tabela-externa='tipos_de_veiculos' data-campo-nome-externo='nome_tipo_de_veiculo' data-id-externo='id_token' data-companion-id='id_token_tipo_de_veiculo' data-companion-results='results_veiculos' data-id-botao='botao_de_envia' value='$valor_campo_externo'>
  <input id='id_token_tipo_de_veiculo' type='hidden' name='id_token_tipo_de_veiculo' value='$valor_campo'>
	</div>
";
}

                    } else {
                        // Caso contrário, crie um campo de entrada padrão
						if ($nomeCampo=='nome_evidencia') {$nomeCampo_temp='Nomeie sua contribuição:'; $largura_text_nome_evidencia="style='flex-basis: 100%;'"; $largura_campo_nome_evidencia="style='flex-basis: 100%;' ";} else {$nomeCampo_temp=$nomeCampo; $largura_campo_nome_evidencia=""; $largura_text_nome_evidencia ="";}
                        echo "<div id='div_entrada_$nomeCampo' class='campo'  $largura_campo_nome_evidencia><label for='$nomeCampo' class='nome_campo'>$nomeCampo_temp</label>";
                        echo "<input type='$inputType' class='imprescindivel' data-id-botao='botao_de_envia' id='$nomeCampo' $largura_text_nome_evidencia name='$nomeCampo' data-tabela='$nomeTabela' data-campo='$nomeCampo' value='$valor_campo'></div>";
                    }
                    break;
                }
            }
        } else {$nome_chave_primaria=$nomeCampo;} // assume que a chave primaria é sempre o primeiro campo
    }
    if ($id_evidencia=="nulo") {
    echo "<input id='botao_de_envia' type='submit' value='Enviar' disabled style='flex-shrink: 0; margin: 10px' onclick='document.getElementById(`div_form_upload`).style.display=`flex`'>";
    }
    echo "
<input id='latitude' type='hidden' name='latitude' value=''>
<input id='longitude' type='hidden' name='longitude' value=''>
<input id='nome_usuario' type='hidden' name='nome_usuario' value='$nome_usuario'>
</form>";
} else {
    echo "A tabela não foi encontrada.";
}

// note que o escolhe_autor estah display: none no começo. Note que tenho que usar os nomes id_token e nome_tipo para o script_inseridencia.js funcionar
if ($id_evidencia!="nulo") 
	{
		$display_escolhe_autor='style="display: flex;"';
		$parametro_saida_string = "saida_html";
		include "buscarAutoresPorEvidencia.php"; // tentando reaproveitar o código. Retornará saida_html com todos os autores e botao de apagar
	} 
		else 
	{
		$display_escolhe_autor='style="display: none;"';
		$saida_html = "<div id='mensagem_não_tem_autores'>Sem registro de autores</div>";
	}
echo '
<div id="div_form_upload" '.$display_escolhe_autor.'>
	<div id="escolhe_autor" class="clearfix" '.$display_escolhe_autor.'>
                <div id="drop_autores" class="dropdown-wrapper campo" data-sql="SELECT nome_pessoa as nome_tipo, id_chave_pessoa as id_token FROM pessoas WHERE nome_pessoa LIKE ?;">
                    	<label>autor:</label>
			<input id="input_autores" type="text" class="search-input shrinkable" autocomplete="off" placeholder="Digite para buscar..." data-tabela="autores_evidencias" data-campo1="id_autor" data-campo2="id_evidencia" data-tabela-externa="pessoas" data-campo-nome-externo="nome_pessoa" data-id-externo="id_chave_pessoa" data-companion-id="id_do_autor"  data-companion-results="results_autores">
			<input id="adiciona_autor" class="shrinkable" type="button" value="adiciona" onclick="grava_e_mostra_os_autores_async();">
	            	<input id="id_do_autor" type="hidden" value="">
    	            <div id="results_autores"  class="results" tabindex="-1"></div>
		</div>
            <input type="hidden" id="last_inserted_id_evidencia_para_autor" value="">
            <div id="mostra_autores">'.$saida_html.'</div>   
    	</div>
</div>
</div>
<script src="script_inseridencia.js"></script>';

echo "<div id='results_evidencias'  class='results' tabindex='-1'></div>
      <div id='results_veiculos' class='results' tabindex='-1'></div>";

echo '
<script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>
</body>
</html>
';

$conn->close();
?>

