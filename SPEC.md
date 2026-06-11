# Projeto: Modulo Moodle `mod_iajudge`

## Objetivo
Criar um modulo de atividade para Moodle 5.2 no qual estudantes enviam codigo em Python, C, Java ou JavaScript, e a plataforma avalia a submissao usando IA, retornando nota, feedback pedagogico e status de processamento.

## Diretrizes de Plataforma
- O alvo oficial do projeto e Moodle 5.2.
- O ambiente de execucao deve ser tratado como PHP 8.3.
- O plugin deve seguir os padroes atuais do Moodle para modulos de atividade.
- A interface visual deve ser construída com templates Mustache.
- O JavaScript deve ser mantido leve e organizado em AMD, apenas como camada de interacao.
- O processamento de IA deve ser assicrono, usando a Task API do Moodle.

## Integracao com IA
- Priorizar a AI API do core do Moodle quando ela estiver disponivel e for adequada ao caso de uso.
- Quando a AI API do core nao cobrir um provedor ou um fluxo especifico, encapsular o acesso em uma camada de adaptadores para manter o restante do plugin independente do provedor.
- Os provedores devem ser tratados como detalhes de infraestrutura, nao como acoplamento da interface.
- A saida esperada da IA deve continuar sendo estruturada, com resposta em JSON validavel.

## Interface do Estudante
- Usar Mustache para renderizar a tela principal de envio, o historico de tentativas e o resultado da avaliacao.
- Exibir um seletor de linguagem do tipo `select`.
- Exibir um editor de codigo embutido, nao um `textarea` simples.
- O editor deve suportar:
  - realce de sintaxe;
  - numeracao de linhas;
  - troca de linguagem conforme a selecao;
  - bloqueio de edicao apos o envio;
  - visualizacao responsiva em desktop e mobile.
- O botao de envio deve impedir duplicidade de submissao enquanto a tarefa async estiver em processamento.

## Interface do Professor
- Usar o editor rico nativo do Moodle para o enunciado.
- Permitir configuracao da rubrica em um campo de texto dedicado.
- Permitir definicao de linguagens habilitadas por atividade.
- Permitir limite de tentativas por estudante.
- O formulario deve seguir a API de moodleform, com validacao server-side e defaults coerentes.

## Administracao Global
- O plugin deve oferecer pagina de configuracao em `settings.php`.
- A configuracao deve cobrir:
  - provedor de IA;
  - chave de API quando aplicavel;
  - base URL ou endpoint;
  - nome do modelo;
  - teste de conexao.
- Se o Moodle 5.2 expuser configuracao de AI API centralizada, o plugin deve respeitar essa configuracao antes de usar valores locais.
- O teste de conexao deve ser separado da interface de envio do estudante.

## Persistencia e Dados
- A tabela principal deve armazenar a instancia da atividade.
- A tabela de submissao deve armazenar linguagem, codigo, status e timestamps.
- A tabela de avaliacao deve armazenar score, feedback e resposta bruta do modelo.
- O esquema deve ser compatível com o XMLDB do Moodle.
- O plugin deve manter compatibilidade com gradebook quando fizer sentido para a atividade.

## Processamento Assincrono
- O envio do estudante cria uma submissao persistida.
- A submissao e colocada em fila para processamento assicrono.
- A task busca a submissao, monta o prompt, chama a IA e grava o resultado.
- O frontend acompanha o status ate a resposta final.

## Padroes Tecnicos
- Preferir APIs modernas do Moodle 5:
  - `core/ajax` quando houver chamadas AJAX;
  - `core/notification` para erros de interface;
  - `core/templates` para renderizacao de blocos dinamicos;
  - Mustache para markup.
- Evitar logica de negocio no JavaScript.
- Evitar HTML montado manualmente em strings quando um template Mustache resolver o mesmo problema.

## Requisitos de Qualidade
- O plugin deve ser instalavel via ZIP no Moodle 5.2.
- O pacote de distribuicao deve ter a raiz correta do plugin.
- O codigo deve ser mantido com clareza de separacao entre:
  - dominio;
  - acesso a IA;
  - interface;
  - persistencia;
  - processamento assicrono.

## Observacoes
- Monaco Editor e aceitavel como dependencia de interface, mas deve ser tratado como detalhe de apresentacao, nao como parte da regra de negocio.
- Se o peso do pacote impedir instalacao web confiavel, deve-se preferir uma integracao mais leve ou um recorte maior dos assets do editor.
