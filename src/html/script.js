
window.onload = function() {
    setTimeout(function() {
        var header = document.getElementById('tempHeader');
        header.style.height = '0px';  /* Reduz a altura para 0 */
        
        // Após a transição ser concluída, remova o cabeçalho do DOM
        header.addEventListener('transitionend', function() {
            if (event.propertyName === 'height') {header.parentNode.removeChild(header)};
        });
        
    }, 1000); /* Espere 5 segundos antes de iniciar a animação */

const container = document.querySelector('#tempHeader');
const element = document.querySelector('#titulo_geral');
const logo_funda = document.querySelector('#logo_funda');

    const initialSize = 1;  // Inicia com 16px, ajuste conforme necessário
   	element.style.fontSize = `${initialSize}px`;

    // Reduz o tamanho da fonte até que o texto caiba no contêiner

let computedStyle = window.getComputedStyle(element);
let fontSize = parseFloat(computedStyle.getPropertyValue('font-size'));
let largura = parseFloat(computedStyle.getPropertyValue('width'));
let margem;
	while (largura * 1.2 < (container.clientWidth - logo_funda.clientWidth )&& element.clientHeight < container.clientHeight * 0.7 ) {
	console.log("ajuste: "+ largura+ " - "+ container.clientWidth);
        fontSize++;
		margem = 0.2 * largura;
        element.style.fontSize = `${fontSize}px`;
		//element.style.margin = `${margem}px`;
largura = parseFloat(computedStyle.getPropertyValue('width'));
    	largura = parseFloat(computedStyle.getPropertyValue('width'));
		element.style.top = `${container.clientHeight/2 - element.clientHeight /2}px`;
		element.style.left = `${logo_funda.clientWidth + (container.clientWidth - logo_funda.clientWidth) /2 - element.clientWidth/2}px`;

	}

}


class token_na_frase {
	constructor(id_token, ordem, nome_tipo_sintatico, id_tipo_sintatico, token, id_categoria) {
		this.id_token = id_token;
		this.ordem = ordem;
		this.nome_tipo_sintatico = nome_tipo_sintatico;
		this.id_tipo_sintatico = id_tipo_sintatico;
		this.token = token;
		this.id_categoria = id_categoria;
	}
}

let matriz_tokens = [];
var conta_entradas_function=0;
function checa_se_desabilita(nome_tipo_sintatico) {

	return true; // forca a desabilitacao temporariamente para a demonstracao. retire esta linha quando quiser usar este botao
	console.log("entradas na funcao para debug:"+conta_entradas_function);
	conta_entradas_function++;
	let lista_tokens = document.querySelectorAll("."+nome_tipo_sintatico);
	console.log("TAMANHO: "+lista_tokens.length+" Nome_tipo_sintatico: "+nome_tipo_sintatico);
	let eh_para_desabilitar=true;
	for (let i = 0; i < lista_tokens.length; i++) {
		let selecionou = lista_tokens[i].getAttribute("data-selecionou");
		if (selecionou == "sim") {
			eh_para_desabilitar = false;
		}
	console.log(i+") selecionou = "+selecionou);
	//	console.log(nome_tipo_sintatico+" nome_token"+lista_tokens.value+" eh para desabilitar: "+ eh_para_desabilitar);
	}
return eh_para_desabilitar;	
} //function checa_se_desabilita




function recicla_frase(id_frase, nom_tipo_sintatico){

let inputs_do_tipo_sintatico = document.querySelectorAll("."+nom_tipo_sintatico);
console.log("opa "+nom_tipo_sintatico);
console.log(inputs_do_tipo_sintatico);
inputs_do_tipo_sintatico.forEach(its => {
	its.value="";
	its.setAttribute("data-selecionou", "nao");
	document.getElementById(its.getAttribute("data-companion")).setAttribute("data-selecionou", "nao");
});

let data_global;
fetch(`busca_tokens_na_frase.php?id_frase=${id_frase}`)
    .then(response => response.json())
    .then(data => {
        //console.log(data);
		data_global=data;
console.log(id_frase);




data_global.forEach(dt => {



let input_text = document.querySelectorAll('.input_class_'+dt.id_categoria); // só tem um dessa classe. Não se preocupe
let marca_input = document.querySelectorAll('.marca_class_'+dt.id_categoria); // só tem um dessa classe. Não se preocupe

	input_text[0].value = dt.nome_token_na_frase;
	input_text[0].setAttribute("data-id-token", dt.id_token);
	input_text[0].setAttribute("data-id-categoria", dt.id_categoria);
	input_text[0].setAttribute("data-ordem", dt.ordem);
	input_text[0].setAttribute("data-selecionou", "sim");
	marca_input[0].setAttribute("data-selecionou", "sim");
});
    })
    .catch(error => {
        console.error("Erro ao buscar tokens:", error);
    });



} // fim recicla_frase

function apaga_frase(id_frase){


fetch('apaga_frase.php', {
	method: 'POST',
    headers: {
        'Content-Type': 'application/json',
    },
    body: JSON.stringify({ id_frase: id_frase }) // sim o javascript permite fazer isso: do lado esquerdo de : eh como se estivesse entre parenteses
})
    .then(response => response.json())
.then(response => {
    //alert(response.message);
})
.catch((error) => {
    alert('Error:', error);
});

} // fim apaga_frase


function manda_tokens(nome_tipo_sintatico, id_tipo_sintatico, matriz_tokens){
matriz_tokens = [];

let lista_tokens = document.querySelectorAll("."+nome_tipo_sintatico);
console.log("lista "+nome_tipo_sintatico);
console.log(lista_tokens);
for (let i = 0; i < lista_tokens.length; i++) {
	let id_token = lista_tokens[i].getAttribute("data-id-token");
	let token_string = lista_tokens[i].value;
	let ordem = lista_tokens[i].getAttribute("data-ordem");
	let id_categoria = lista_tokens[i].getAttribute("data-id-categoria");
	let token = new token_na_frase(id_token, ordem, nome_tipo_sintatico, id_tipo_sintatico, token_string, id_categoria);
	console.log(token);
	if (token.token.length > 0) {matriz_tokens.push(token);}
}

if (matriz_tokens.length ===0) {return;}

fetch('grava_tokens.php', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
    },
    body: JSON.stringify(matriz_tokens),
})
.then(response => response.json())
.then(response => {
    //alert(response.message);
})
.catch((error) => {
    alert('Error:', error);
});

} // fim manda_tokens


function hideResults(itz) {
//			console.log("entrou");
//			console.log(itz);
			itz.innerText = '';
            setTimeout(function () {itz.innerText = '';}, 400); // bizarrices do browser: sem esse timeout ele não apaga quando é enter.
            currentSelection = -1;
        }

document.addEventListener("DOMContentLoaded", function() {
    const dropdowns = document.querySelectorAll('.dropdown-wrapper');
	const searchInputs = document.querySelectorAll('.search-input');
	const botoes_grava = document.querySelectorAll('.botao');
	const botoes_delecao = document.querySelectorAll('.deletar');
	const botoes_reciclagem = document.querySelectorAll('.reciclar'); 
	const tipos_automatas = document.querySelectorAll('.tipo_automata'); 
 
	botoes_grava.forEach(bg => {
		bg.addEventListener ('click', 
		 		function () {
					manda_tokens(bg.getAttribute("data-nome-tipo-sintatico"),bg.getAttribute("data-id-tipo-sintatico"), matriz_tokens);							
					setTimeout(function () {window.location.href = "ppapdi.php";}, 1000)
					console.log(matriz_tokens);	
				});
		});

	botoes_delecao.forEach(bd => {
		bd.addEventListener ('click', 
		 		function () {
					id_frase=bd.getAttribute("data-id-frase");
					let resposta_confirm = confirm("Você realmente quer apagar a frase com idenficador "+id_frase+"?");
					if (resposta_confirm) {apaga_frase(id_frase); setTimeout(function () {window.location.href = "ppapdi.php";}, 1000)}								
				});
		});

	botoes_reciclagem.forEach(br => {
		br.addEventListener ('click', 
		 		function () {
					id_frase=br.getAttribute("data-id-frase");
					recicla_frase(id_frase, br.getAttribute("data-nome-elemento-sintatico")); 
				});
		});

	tipos_automatas.forEach(ta => {
		ta.addEventListener ('click', 
		 		function (e) {
					//alert("tipo_automata"); // este alert eh importante para testar se o evento estah sendo ativado por divs internos ao div tipo_automata. 
			        let rDiv = document.querySelectorAll('.results');
					rDiv.forEach(rd => {
							hideResults(rd);
						}
					);
				});
		});
//		searchInputs.forEach(si=>
//			{
//			  			}
//		
//		);
	
  
 	dropdowns.forEach(dropdown => {
        const searchInput = dropdown.querySelector('.search-input');
        const resultsDiv = dropdown.querySelector('.results');
		searchInput.setAttribute("data-selecionou","nao");
		document.getElementById(searchInput.getAttribute("data-companion")).setAttribute("data-selecionou","nao");
		resultsDiv.id="Res_"+dropdown.id;
        let currentSelection = -1;
        let currentItems = [];

        function displayResults(items, ekey) {
            resultsDiv.innerHTML = items.map((item, index) => 
                `<div class="item${index === currentSelection ? ' selected' : ''}" data-id-token="${item.id_chave_token}" data-index="${index}">${item.nome_token}</div>`
            ).join('');

			resultsDiv.querySelectorAll('.item').forEach(item => {
                item.addEventListener('click', function(e) {
	                e.stopPropagation(); 
					item.parentElement.parentElement.children[0].setAttribute("data-selecionou","sim");
					document.getElementById(item.parentElement.parentElement.children[0].getAttribute("data-companion")).setAttribute("data-selecionou","sim");
					console.log("clicou");
//					console.log(item.parentElement.parentElement.children[0]);
                    searchInput.value = e.target.innerText;
					document.getElementById("botao_"+searchInput.getAttribute("data-id-tipo-elemento-sintatico")).disabled=true; // temporariamente true, retorne para false quando sair do modo de demonstracao
					searchInput.setAttribute("data-id-token",e.target.getAttribute("data-id-token"));
                    hideResults(resultsDiv);
             });
			 		
  				 if (item.classList.contains('selected')) {
				 	  
				      if (ekey == 'ArrowDown') { if (item.offsetTop + item.clientHeight > resultsDiv.scrollTop + resultsDiv.clientHeight || item.offsetTop < resultsDiv.scrollTop) {resultsDiv.scrollTop = item.offsetTop - resultsDiv.clientHeight + item.clientHeight;}}
				      if (ekey == 'ArrowUp') {console.log("resultTop: "+resultsDiv.offsetTop+" resultHeight: "+resultsDiv.clientHeight+" itemTop: "+item.offsetTop + "scrollTop: "+ resultsDiv.scrollTop); if (item.offsetTop < resultsDiv.scrollTop || item.offsetTop > resultsDiv.scrollTop + resultsDiv.clientHeight) { resultsDiv.scrollTop = item.offsetTop; console.log("sobe");}}
				 	  console.log('item how come? '+  resultsDiv.scrollTop + " soma: " +(item.offsetTop - resultsDiv.clientHeight + item.clientHeight));
    			 }
          });
			resultsDiv.style.left = searchInput.getBoundingClientRect().left;
			resultsDiv.style.top = searchInput.getBoundingClientRect().top;
		
        }

	
		
        searchInput.addEventListener('blur', function() {
					if (searchInput.value == ""){
					searchInput.setAttribute("data-selecionou", "nao"); 
					document.getElementById(searchInput.getAttribute("data-companion")).setAttribute("data-selecionou","nao");
					document.getElementById("botao_"+searchInput.getAttribute("data-id-tipo-elemento-sintatico")).disabled=checa_se_desabilita(searchInput.getAttribute("data-nome-tipo-sintatico"));;
					}
				//		setTimeout(function () {hideResults(searchInput.parentElement.children[1]);}, 100);
					//	alert("catso");
			
		});

		searchInput.addEventListener
			  (
			  	'focus', function () 
					{
						searchInputs.forEach(sub_si=>{
			  			if (sub_si.getAttribute("data-selecionou")=="nao") {sub_si.value=""; sub_si.setAttribute("data-id-token","")};
						console.log('sub_si -> '+ sub_si.value);
						if (sub_si != searchInput) {setTimeout(function () {hideResults(sub_si.parentElement.children[1]);}, 300);}
						});
			   
					
					}
			  );

		searchInput.addEventListener
			  (
			  	'click', function (e) 
					{
				e.stopPropagation();
				setTimeout(function () {fetch(`carrega_tokens_inseridencia.php?term=&query=`+dropdown.getAttribute('data-sql'))
                .then(response => response.json())
                .then(data => {
                    currentItems = data;
					console.log(data);
                    displayResults(currentItems,'');
                })
                .catch(error => console.error('Erro:', error));
				}, 350);

										
					}
			  );


         searchInput.addEventListener('input', function() {
            const searchTerm = searchInput.value;
			
			if (searchTerm.length === 0) 
				{ 
					searchInput.setAttribute("data-selecionou", "nao"); 
					document.getElementById(searchInput.getAttribute("data-companion")).setAttribute("data-selecionou","nao");
					document.getElementById("botao_"+searchInput.getAttribute("data-id-tipo-elemento-sintatico")).disabled=checa_se_desabilita(searchInput.getAttribute("data-nome-tipo-sintatico"));;
				}

            if (searchTerm.length < 0) { // eu acho que coloquei isso para nunca acontecer, porque queria o drop sempre aberto, mas nao estah acontecendo assim, por ora - desconsidere esta mensagem se estiver
                hideResults(resultsDiv);
                return;
            }

            fetch(`carrega_tokens_inseridencia.php?term=${searchTerm}&query=`+dropdown.getAttribute('data-sql'))
                .then(response => response.json())
                .then(data => {
                    currentItems = data;
					console.log(data);
                    displayResults(currentItems,'');
                })
                .catch(error => console.error('Erro:', error));
        });
        searchInput.addEventListener('keydown', function(e) {
			document.getElementById("botao_"+searchInput.getAttribute("data-id-tipo-elemento-sintatico")).disabled=checa_se_desabilita(searchInput.getAttribute("data-nome-tipo-sintatico"));
			if (e.key==='Tab') {} // por algum motivo este if eh necessario para que o if (['Tab'].includes funcionar
            if (e.key === 'ArrowDown') {
                e.preventDefault();
                if (resultsDiv.innerHTML=="") {searchInput.click();}
				currentSelection++;
                if (currentSelection >= currentItems.length) currentSelection = 0;
			}

            if (e.key === 'ArrowUp') {
                e.preventDefault(); 
                currentSelection--;
                if (currentSelection < 0) currentSelection = currentItems.length - 1;
            }

            if (e.key === 'Enter' && currentSelection > -1) {
                e.preventDefault(); 
				searchInput.setAttribute("data-selecionou","sim");
				document.getElementById(searchInput.getAttribute("data-companion")).setAttribute("data-selecionou","sim");
				searchInput.value = currentItems[currentSelection].nome_token;
				document.getElementById("botao_"+searchInput.getAttribute("data-id-tipo-elemento-sintatico")).disabled=true; // temporariamente true, retorne para false quando sair do modo de demonstracao
				searchInput.setAttribute("data-id-token", currentItems[currentSelection].id_chave_token);// tem que substituir por id
				console.log("hideresults");
                hideResults(resultsDiv);
            }

            if (e.key === 'Escape') {
                hideResults(resultsDiv);
            }

		//		console.log(" RESULTADO: "+document.getElementById("botao_"+searchInput.getAttribute("data-id-tipo-elemento-sintatico")).disabled);
            if (['ArrowDown', 'ArrowUp', 'Enter', 'Escape', 'Tab'].includes(e.key)) {
                if(e.key!="Tab") {displayResults(currentItems, e.key);}
				else {hideResults(resultsDiv);}
            } else {
						searchInput.setAttribute("data-selecionou","nao");
						document.getElementById(searchInput.getAttribute("data-companion")).setAttribute("data-selecionou","nao");
				   }

        });
    });
});
