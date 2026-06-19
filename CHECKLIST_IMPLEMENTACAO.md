# Checklist tecnica - `codejudge` integrado ao Quiz

## Escopo
Implementar `codejudge` como um tipo de questao do Moodle para uso dentro do Quiz e do question bank, com editor de codigo e correcao por IA.

Legenda:
- [x] Feito em codigo e considerado estruturalmente presente.
- [~] Parcial: existe codigo, mas ainda nao foi validado no Moodle/Quiz ou ainda nao fecha o fluxo funcional.
- [ ] Pendente.

## Checkpoint de plano
Este documento passa a ser o painel de controle da implementacao. A regra de rastreabilidade e separar claramente:
- o que ja existe em codigo;
- o que ja foi validado no Moodle/Quiz;
- o que ainda bloqueia o fluxo real do aluno, da tentativa, da revisao e da nota.

Antes de novos codigos funcionais, o proximo trabalho deve partir do marco ativo definido em "Proximo marco".

## Status atual
- [x] Estrutura base do question type `qtype_codejudge` criada.
- [x] Tabela de opcoes da questao criada.
- [x] Formulario de edicao, renderer e strings basicas criados.
- [x] Upgrade XMLDB do `qtype_codejudge` corrigido para usar a API esperada do Moodle.
- [x] Persistencia das opcoes basicas endurecida com defaults defensivos.
- [x] Helper de linguagens e editor inicial de codigo adicionados.
- [x] Plugin instalado/atualizado em Moodle e reconhecido como `Coding question`.
- [x] Formulario de criacao da questao abre no question bank apos ajuste do nome `edit_codejudge_form.php`.
- [x] Criacao, salvamento, reabertura e edicao no question bank testados no Moodle.
- [x] Preview da questao renderiza o enunciado corretamente apos correcao.
- [x] Rubrica de correcao mantida como configuracao interna e removida da visualizacao do aluno.
- [x] Strings em portugues cobrem `pt_br` e fallback `pt` para textos do preview.
- [x] Editor de codigo validado no fluxo real do Quiz.
- [x] Pipeline de IA dispara no ciclo real da tentativa e aplica nota/revisao no Moodle.
- [~] Testes unitarios basicos existem, mas ainda faltam testes de persistencia, integracao com question engine e fluxo de Quiz.
- [~] Backup/restore de opcoes existe, mas ainda precisa ser validado em backup/restauracao real de quizzes.
- [ ] Integracao completa com o question engine do Moodle ainda precisa ser implementada/validada.
- [x] Nota e comentario da autocorrecao entram no fluxo normal de revisao, Quiz e gradebook.
- [~] Fase de modernizacao do editor de codigo implementada com CodeMirror 6 em codigo; falta validacao real no Moodle/Quiz.
- [~] Feedback imediato com multiplas tentativas tem suporte assincrono inicial em codigo; falta validacao real no Moodle e decisao final para steps intermediarios do modo `interactive`.
- [~] Privacy provider implementado em codigo; falta validar export/delete em Moodle instalado.

## Status real por fase

### Fase 1 - Consolidar o question type
- [x] Raiz do plugin, versionamento, install/upgrade, strings, form, renderer e classe de questao existem.
- [x] Persistencia das opcoes especificas da questao existe.
- [x] `upgrade.php` usa chamadas XMLDB compativeis com Moodle.
- [x] Defaults de linguagem e altura do editor sao normalizados ao salvar/carregar.
- [x] Strings basicas em ingles e portugues estao presentes para criacao/edicao.
- [x] Compatibilidade inicial com question bank validada em Moodle instalado: plugin aparece e form abre.
- [x] Instalar/atualizar, criar, salvar, reabrir e editar questao no banco de questoes validado em Moodle.
- [x] Preview da questao renderiza o enunciado corretamente.
- [x] Confirmar salvar, reabrir e editar uma questao com campos persistidos.

### Fase 2 - Editor de codigo na questao
- [x] Renderer cria campo de codigo e acopla AMD `qtype_codejudge/editor`.
- [x] AMD suporta indentacao, resize, multiplas instancias e readonly basico.
- [x] O editor leve foi validado em tentativa real, revisao, preview e navegadores suportados.
- [~] Confirmar que o editor nao interfere no submit normal do Quiz: falhou ao navegar para proxima pagina, interceptacao removida do AMD e reteste pendente.

### Fase 3 - Compatibilidade com o Quiz
- [x] `question.php` define dados esperados, completude, resumo e comparacao de resposta.
- [x] A questao deixou de forcar `manualgraded` e passou a expor resultado automatico provisÃ³rio `needsgrading`.
- [x] Validar adicionar `codejudge` a um Quiz.
- [x] Validar iniciar tentativa, salvar, retomar e revisar.
- [x] Validar tentativa com multiplas questoes `codejudge` e quiz misto.

### Fase 4 - Pipeline backend de correcao por IA
- [x] Tabela `qtype_codejudge_grading` existe para fila/resultados.
- [x] Helper monta prompt e enfileira task adhoc.
- [x] Task chama provider de IA e persiste `score`, `feedback`, `rawresponse` e erro.
- [~] Web services AJAX existem para enfileirar e consultar status, mas devem ser tratados como apoio/teste/admin, nao como gatilho principal do Quiz.
- [x] Interceptacao do submit/navegacao do Quiz via AMD foi removida.
- [x] Definir gatilho backend seguro para correcao apos finalizacao/submissao da tentativa.
- [x] Localizar respostas `codejudge` dentro da tentativa do Quiz.
- [x] Enfileirar uma unica correcao por questao, tentativa e resposta.
- [x] Garantir idempotencia e evitar duplicidade de fila em salvar/retomar/reenvio/refresh.

### Fase 5 - Revisao e gradebook
- [x] Aplicar `score` da IA ao grade da questao via `manual_grade`.
- [x] Exibir comentario da autocorrecao na revisao da tentativa como comentario manual do question engine.
- [x] Atualizar/recalcular tentativa e gradebook quando a task terminar.
- [x] Representar estado aguardando correcao, corrigida ou erro para aluno/professor.
- [~] Definir comportamento para reprocessamento, nova tentativa, timeout e erro da IA: idempotencia por tentativa/resposta existe; timeout e reprocessamento manual seguem pendentes.

### Fase 6 - Modernizacao do editor de codigo
- [x] Escolher editor open source para substituir o editor leve atual, com decisao registrada entre Monaco, CodeMirror 6 ou alternativa equivalente.
- [x] Definir estrategia de empacotamento local dos assets, sem dependencia de CDN em producao.
- [x] Integrar o editor moderno ao AMD `qtype_codejudge/editor` mantendo o campo Moodle como fonte enviada ao question engine.
- [x] Mapear linguagem configurada na questao para o modo/syntax highlighting do editor.
- [x] Garantir suporte a multiplas instancias na mesma pagina em codigo.
- [x] Garantir readonly correto em preview/revisao em codigo.
- [x] Manter fallback para `textarea` caso o editor moderno falhe ou nao carregue.
- [~] Validar que o editor moderno nao intercepta submit, navegacao, salvamento, retomada ou revisao do Quiz.
- [~] Validar acessibilidade minima: foco, teclado, contraste e uso sem mouse.
- [x] Atualizar build AMD e pacote Moodle apos a troca.

### Fase 7 - Feedback imediato e multiplas tentativas
- [x] Remover a decisao fixa de sempre usar `manualgraded` e respeitar `immediatefeedback`/`interactive` quando o Quiz solicitar esses comportamentos.
- [~] Definir comportamento especifico do `qtype_codejudge` para checks durante tentativa aberta: implementado como correcao assincrona acionada apos renderizacao do step.
- [x] Capturar cada resposta/check como step proprio do question engine.
- [~] Enfileirar correcao por `questionattemptid`, `questionattemptstepid` e resposta; numero da tentativa interativa ainda depende do step do question engine.
- [x] Garantir idempotencia por step sem impedir nova tentativa/check legitimo da mesma questao.
- [~] Definir UX para latencia da IA: status aguardando/processando/corrigido/erro existe; polling e bloqueio temporario ainda nao foram implementados.
- [~] Atualizar estado, nota e feedback da questao no question engine quando a correcao de um check terminar: feedback fica disponivel inline durante tentativa aberta; nota so deve ser aplicada apos submissao final para nao quebrar a sequencia do Quiz.
- [~] Exibir comentario formativo da autocorrecao inline na questao para `immediatefeedback`/`interactive`: implementado em codigo, falta validacao real.
- [x] Evitar que resultado atrasado de uma tentativa anterior sobrescreva resposta mais nova.
- [~] Validar Quiz com feedback imediato e multiplas tentativas: falhou com erro de sequencia ao aplicar nota durante tentativa aberta; corrigido para reteste.
- [ ] Validar Quiz em modo interativo com multiplas tentativas, quando aplicavel.
- [~] Definir fallback quando a IA falhar durante uma tentativa aberta: erro fica persistido/renderizado, politica pedagogica final pendente.

### Fase 8 - Portugol para algoritmos iniciantes
- [x] Adicionar `Portugol` ao seletor de linguagem.
- [x] Adicionar descricao: "Use Portugol para escrever um algoritmo estruturado, com comandos como leia, escreva, se, senao, enquanto, para, variaveis e atribuicoes."
- [x] Evitar nomenclatura principal "Pseudocodigo" para nao incentivar respostas vagas em linguagem natural.
- [x] Mapear Portugol para highlight/fallback adequado no editor.
- [x] Ajustar prompt da IA com regra central: avaliar logica algoritmica estruturada e nao aceitar texto puramente descritivo.
- [x] Exigir formato minimo: variaveis, entrada, processamento, saida e estrutura logica suficiente.
- [x] Aceitar variacoes comuns de sintaxe de Portugol/pseudocodigo formal.
- [x] Penalizar respostas vagas que apenas descrevem a ideia sem passos algoritmicos.
- [x] Penalizar respostas em Python/C/JavaScript/outra linguagem quando a questao exigir Portugol.
- [x] Aplicar regra geral de conformidade de linguagem para Python, C, Java, JavaScript e Portugol.
- [x] Adicionar testes de normalizacao/validacao da nova linguagem.
- [ ] Validar questao real de algoritmo iniciante usando Portugol.

### Fase 9 - Admin e seguranca
- [x] Settings de provider/modelo/chave/base URL e teste de conexao existem.
- [x] `db/access.php` revisado: sem capabilities customizadas nesta fase; o plugin usa permissoes core do Moodle para question bank, Quiz e administracao.
- [x] Privacy provider declara tabela de correcao, dados enviados ao provedor de IA, exportacao e delecao por usuario/contexto.
- [x] Web service de enfileiramento restrito a administradores como superficie auxiliar/teste/admin.
- [x] Web service de status restringe acesso ao proprio usuario ou a quem pode ver relatorios do Quiz.
- [ ] Validar privacy export/delete em Moodle instalado.

### Fase 10 - Qualidade
- [x] Testes unitarios existem para linguagem, helper de prompt e resposta.
- [x] Testes de persistencia de opcoes da questao adicionados para insert/update/defaults.
- [x] Testes de contrato do privacy provider adicionados.
- [x] Testes de contrato backup/restore adicionados para opcoes da questao.
- [~] Testes de integracao com question engine existem parcialmente em `question_test.php`; fluxo real de aplicacao da nota ainda depende de Moodle instalado/Behat.
- [~] Testes de Quiz: tentativa, salvamento, revisao, nota e feedback cobertos por plano manual; falta automatizacao Behat.
- [ ] Validacao manual fim a fim em Moodle.

## Proximo marco
Marco ativo sugerido: **Marco 9 - validar admin, privacy, backup/restore antes da qualidade final**.

Objetivo imediato:
- instalar a versao 0.1.23;
- validar exportacao e delecao de dados pessoais pelo privacy API;
- validar permissoes dos web services auxiliares;
- validar backup/restore de curso com Quiz contendo `codejudge`;
- confirmar que o Quiz restaurado corrige, revisa e publica nota corretamente;
- registrar qualquer pendencia antes da fase de qualidade final.

Nao avancar para qualidade final ampla antes de validar privacy e backup/restore em Moodle instalado.

## Ordem de execucao por marcos

### Marco 1 - Estabilizar o `qtype_codejudge` instalavel e editavel no banco de questoes
Arquivos principais:
- `question/type/codejudge/version.php`
- `question/type/codejudge/db/install.xml`
- `question/type/codejudge/db/upgrade.php`
- `question/type/codejudge/questiontype.php`
- `question/type/codejudge/edit_codejudge_form.php`
- `question/type/codejudge/lang/*/qtype_codejudge.php`
- `question/type/codejudge/pix/*`

Comportamento esperado:
- o Moodle reconhece o plugin como tipo de questao;
- o professor cria e edita uma questao `codejudge`;
- linguagem, rubrica, codigo inicial e altura do editor sao persistidos;
- a questao reabre no form com os mesmos valores salvos.
- o preview da questao mostra o enunciado antes do editor.
- a rubrica de correcao nao aparece para o aluno no preview/tentativa.
- upgrade de versoes anteriores nao quebra por chamadas XMLDB invalidas.

Teste manual no Moodle:
- instalar/atualizar plugins;
- criar uma questao `codejudge` no question bank;
- salvar, editar novamente e conferir os campos;
- abrir preview e confirmar enunciado/editor;
- confirmar que a rubrica nao aparece para o aluno;
- duplicar/reutilizar a questao, se aplicavel.

Teste automatizado quando possivel:
- PHPUnit para persistencia de opcoes em `questiontype.php`;
- PHPUnit para validacao de linguagem, rubrica e altura do editor.

Decisoes a registrar se ficarem fora de escopo:
- quais languages sao suportadas oficialmente neste marco;
- se capabilities customizadas ficam adiadas para o Marco 9.

### Marco 2 - Validar renderizacao e submissao no Quiz sem IA
Arquivos principais:
- `question/type/codejudge/question.php`
- `question/type/codejudge/renderer.php`
- `question/type/codejudge/amd/src/editor.js`
- `question/type/codejudge/amd/build/editor.min.js`

Comportamento esperado:
- a questao pode ser adicionada a um Quiz;
- o aluno ve o editor dentro da tentativa;
- o codigo digitado e salvo como resposta da tentativa;
- salvar, retomar e revisar nao perdem o codigo;
- readonly funciona em revisao/preview.

Teste manual no Moodle:
- criar Quiz com uma questao `codejudge`;
- iniciar tentativa como aluno;
- preencher codigo, navegar, salvar e retomar;
- finalizar e revisar a tentativa;
- testar mais de uma questao `codejudge` na mesma tentativa.

Teste automatizado quando possivel:
- PHPUnit para `get_expected_data`, `is_complete_response`, `is_same_response` e serializacao;
- teste Behat ou equivalente para renderizacao/submissao basica no Quiz.

Decisoes a registrar se ficarem fora de escopo:
- se o editor continua textarea leve neste marco ou se havera Monaco depois;
- manter o editor sem interceptar submit/navegacao do Quiz; a IA deve ser ligada por gatilho backend.

### Marco 3 - Ligar a fila IA ao ciclo backend correto da tentativa
Arquivos principais:
- `question/type/codejudge/question.php`
- `question/type/codejudge/classes/local/grading_helper.php`
- `question/type/codejudge/classes/task/grade_submission.php`
- `question/type/codejudge/classes/external/queue_grading.php`
- `question/type/codejudge/classes/external/check_status.php`
- `question/type/codejudge/db/services.php`
- `question/type/codejudge/db/install.xml`

Comportamento esperado:
- a correcao e disparada no backend, sem interceptar submit/navegacao do Quiz;
- a correcao e disparada uma unica vez para uma resposta final/gradavel;
- a fila fica vinculada ao `question_attempt` e, quando possivel, ao step correto;
- reenvio, retomar tentativa e refresh nao criam duplicidades indevidas;
- erros de IA ficam registrados sem quebrar a tentativa.

Teste manual no Moodle:
- finalizar tentativa e verificar registro `queued/processing/graded/error`;
- repetir salvamento/retomada para verificar idempotencia;
- testar provider configurado e erro de provider.

Teste automatizado quando possivel:
- PHPUnit para montagem de prompt e normalizacao;
- teste de idempotencia da fila;
- teste da task com provider fake/mock.

Decisoes a registrar se ficarem fora de escopo:
- se a correcao roda ao salvar tentativa, ao finalizar tentativa ou por outro evento;
- politica para timeout, erro e reprocessamento manual.

### Marco 4 - Aplicar nota/feedback no question engine e revisao
Arquivos principais:
- `question/type/codejudge/question.php`
- `question/type/codejudge/renderer.php`
- `question/type/codejudge/classes/task/grade_submission.php`
- possiveis classes auxiliares de estado/feedback do question engine.

Comportamento esperado:
- `score` da IA vira nota da questao no question engine;
- feedback aparece na revisao da tentativa;
- estado da questao reflete aguardando correcao, corrigida, timeout ou erro;
- a experiencia do professor/aluno fica coerente com o fluxo normal do Quiz.

Teste manual no Moodle:
- finalizar tentativa e aguardar task;
- revisar tentativa antes e depois da task;
- conferir nota da questao;
- conferir feedback pedagogico.

Teste automatizado quando possivel:
- teste de integracao com question engine para aplicar nota;
- teste de renderizacao de feedback na revisao.

Decisoes a registrar se ficarem fora de escopo:
- se a nota da IA sera 0-100 convertida para `maxmark` da questao;
- como tratar questao enquanto a IA ainda esta processando.

### Marco 5 - Gradebook, revisao e fechamento do fluxo de nota
Arquivos principais:
- `question/type/codejudge/question.php`
- `question/type/codejudge/renderer.php`
- `question/type/codejudge/classes/local/grading_helper.php`
- `question/type/codejudge/classes/task/grade_submission.php`

Comportamento esperado:
- nota final do Quiz e gradebook refletem a nota da questao;
- comentario da autocorrecao aparece na revisao;
- estado de aguardando, corrigido e erro fica claro para aluno/professor;
- feedback completo aparece no comentario manual, sem duplicidade no bloco de status.

Teste manual no Moodle:
- finalizar tentativa e aguardar task;
- conferir nota da questao, nota da tentativa e gradebook;
- revisar tentativa e confirmar comentario da autocorrecao;
- validar labels em portugues e ingles.

Teste automatizado quando possivel:
- teste de integracao com question engine para aplicar nota;
- teste de recalculo de tentativa;
- teste de renderizacao de status/revisao.

Decisoes a registrar se ficarem fora de escopo:
- politica para reprocessamento manual;
- politica para timeout e erro persistente da IA.

### Marco 6 - Modernizar o editor de codigo
Arquivos principais:
- `question/type/codejudge/amd/src/editor.js`
- `question/type/codejudge/amd/build/editor.min.js`
- `question/type/codejudge/renderer.php`
- `question/type/codejudge/lang/*/qtype_codejudge.php`
- arquivos de build/assets do editor escolhido, se forem adicionados ao plugin.

Comportamento esperado:
- o editor leve atual e substituido por editor open source moderno;
- syntax highlighting, indentacao e ergonomia de codigo ficam melhores que o textarea atual;
- multiplas questoes `codejudge` na mesma pagina funcionam sem conflito;
- readonly funciona em preview/revisao;
- o valor do editor e sincronizado com o campo submetido ao question engine;
- existe fallback para `textarea` se o editor moderno falhar;
- submit, navegacao, salvar, retomar e finalizar tentativa continuam usando o fluxo normal do Moodle.

Teste manual no Moodle:
- criar/editar questao com codigo inicial;
- abrir preview e confirmar editor, enunciado e readonly quando aplicavel;
- responder em Quiz com uma questao `codejudge`;
- responder em Quiz com multiplas questoes `codejudge`;
- responder em Quiz misto;
- navegar entre paginas, salvar, retomar, finalizar e revisar;
- testar pelo menos Chrome/Edge e Firefox, quando possivel.

Teste automatizado quando possivel:
- teste JS unitario do wrapper/sincronizacao, se o ambiente permitir;
- Behat para tentativa com editor e submissao;
- PHPUnit apenas para garantir que renderer preserva atributos esperados.

Decisoes a registrar se ficarem fora de escopo:
- editor escolhido: CodeMirror 6, por ser open source, modular, mais leve que Monaco para empacotar no plugin e simples de carregar como AMD local no Moodle.
- Monaco descartado neste ciclo por maior peso de assets e maior complexidade de empacotamento/worker em ambiente Moodle.
- suporte inicial de highlight: Python, C via modo C/C++, Java e JavaScript, alinhado as linguagens configuraveis atuais.
- nivel minimo de acessibilidade aceito neste ciclo: foco por teclado, label ARIA, readonly funcional e fallback para `textarea`.

### Marco 7 - Feedback imediato com multiplas tentativas
Arquivos principais:
- `question/type/codejudge/question.php`
- `question/type/codejudge/renderer.php`
- `question/type/codejudge/classes/local/grading_helper.php`
- `question/type/codejudge/classes/task/grade_submission.php`
- `question/type/codejudge/classes/observer.php`
- `question/type/codejudge/db/events.php`
- `question/type/codejudge/db/install.xml`
- `question/type/codejudge/db/upgrade.php`
- `question/type/codejudge/amd/src/editor.js`
- `question/type/codejudge/amd/build/editor.min.js`
- testes novos em `question/type/codejudge/tests/*`

Comportamento esperado:
- o `qtype_codejudge` respeita `immediatefeedback` e `interactive` quando esses forem os comportamentos preferidos do Quiz;
- cada acao de verificar/check durante tentativa aberta gera ou referencia um step rastreavel;
- cada resposta/check pode ser corrigido sem depender da finalizacao da tentativa inteira;
- a fila diferencia tentativas/checks legitimos da mesma questao e continua idempotente para refresh/reenvio acidental;
- resultado atrasado de uma correcao antiga nao sobrescreve resposta, nota ou feedback de um step mais recente;
- o aluno ve estado coerente enquanto a IA processa: aguardando, processando, corrigido ou erro;
- a nota e o feedback do check atual entram no question engine sem quebrar revisao, nova tentativa e gradebook;
- quando a IA falhar ou demorar demais, o Moodle mostra fallback pedagogicamente seguro.

Teste manual no Moodle:
- criar Quiz configurado como feedback imediato;
- habilitar multiplas tentativas na questao/Quiz quando aplicavel;
- responder uma questao `codejudge` e acionar check/verificar;
- alterar a resposta e acionar novo check;
- confirmar que cada check gera correcao separada ou estado separado;
- simular latencia da IA e confirmar mensagem de processamento;
- confirmar que resultado atrasado nao substitui a correcao mais recente;
- finalizar tentativa e revisar historico, nota e comentario;
- repetir em Quiz misto.

Teste automatizado quando possivel:
- PHPUnit para selecao de comportamento em `make_behaviour`;
- teste de idempotencia por `questionattemptstepid` e hash da resposta;
- teste de aplicacao de resultado apenas quando o step ainda e o step corrente esperado;
- teste com provider fake/mock simulando latencia, erro e respostas fora de ordem;
- Behat para Quiz com feedback imediato e multiplas tentativas.

Decisoes a registrar se ficarem fora de escopo:
- se a correcao durante check sera realmente assincrona com polling ou se o check apenas marcara "aguardando correcao";
- se o botao/check deve bloquear nova submissao ate a IA responder;
- tempo maximo aceitavel antes de mostrar timeout;
- politica para reaproveitar resultado quando o codigo nao mudou;
- se o modo `interactive` e o modo `immediatefeedback` terao o mesmo tratamento ou fluxos separados;
- como representar nota provisoria durante tentativa aberta.

### Marco 8 - Portugol para algoritmos iniciantes
Arquivos principais:
- `question/type/codejudge/classes/local/language_helper.php`
- `question/type/codejudge/classes/local/grading_helper.php`
- `question/type/codejudge/edit_codejudge_form.php`
- `question/type/codejudge/renderer.php`
- `question/type/codejudge/lang/en/qtype_codejudge.php`
- `question/type/codejudge/lang/pt_br/qtype_codejudge.php`
- `question/type/codejudge/lang/pt/qtype_codejudge.php`
- `question/type/codejudge/amd/src/codemirror6_entry.js`
- `question/type/codejudge/amd/src/codemirror6.js`
- `question/type/codejudge/amd/build/codemirror6.min.js`
- `question/type/codejudge/tests/*`

Comportamento esperado:
- o professor pode selecionar `Portugol` como linguagem da questao;
- a descricao da linguagem orienta o aluno a escrever algoritmo estruturado, nao texto livre;
- a IA recebe instrucao especifica para avaliar Portugol como algoritmo formal estruturado;
- respostas em texto puramente descritivo nao sao aceitas como solucao completa;
- solucoes com sintaxe formal ou semi-formal de Portugol sao aceitas quando tiverem entrada, processamento e saida claros;
- ausencia de declaracao formal de variaveis nao e penalizada quando o uso das variaveis estiver claro;
- variacoes comuns como `leia`, `ler`, `entrada`, `escreva`, `imprimir`, `mostrar`, `se`, `entao`, `senao`, `fimse`, `para`, `enquanto`, `faca`, `fimpara` e `fimenquanto` sao aceitas;
- atribuicoes com `<-`, `â†`, `=` ou `recebe` sao aceitas;
- a avaliacao continua considerando todos os casos do problema, nao apenas a forma da escrita.

Regra central para a IA:
- Quando a linguagem escolhida for Portugol, avalie a logica algoritmica estruturada.
- Nao aceite respostas em texto puramente descritivo.
- A solucao deve apresentar comandos claros de entrada, processamento e saida, usando estrutura semelhante a Portugol/pseudocodigo formal.

Formato minimo exigido:
- declaracao ou uso claro de variaveis;
- comando de entrada, como `leia(...)`;
- processamento com atribuicao, condicao ou repeticao;
- comando de saida, como `escreva(...)`;
- estrutura logica suficiente para resolver todos os casos do problema.

Exemplo aceitavel formal:
```text
algoritmo "contador_de_vogais"

var
   palavra: caractere
   letra: caractere
   contador, i: inteiro

inicio
   leia(palavra)
   contador <- 0

   para i de 1 ate tamanho(palavra) faca
      letra <- minusculo(palavra[i])

      se letra = "a" ou letra = "e" ou letra = "i" ou letra = "o" ou letra = "u" entao
         contador <- contador + 1
      fimse
   fimpara

   escreva(contador)
fimalgoritmo
```

Exemplo aceitavel menos formal:
```text
leia palavra
contador <- 0

para cada letra em palavra faÃ§a
   letra <- minusculo(letra)

   se letra for "a" ou "e" ou "i" ou "o" ou "u" entÃ£o
      contador <- contador + 1
   fimse
fimpara

escreva contador
```

Exemplo nao aceitavel como solucao completa:
```text
Contar as vogais da palavra e imprimir a quantidade.
```

Rubrica interna especifica para Portugol:
- Se a linguagem da submissao for Portugol, avalie se a resposta esta escrita como algoritmo estruturado.
- A resposta deve conter comandos explicitos de entrada, processamento e saida.
- Aceite variacoes de sintaxe como `leia`, `ler`, `entrada`, `escreva`, `imprimir`, `mostrar`, `se`, `entao`, `senao`, `fimse`, `para`, `enquanto`, `faca`, `fimpara` e `fimenquanto`.
- Aceite atribuicoes com `<-`, `â†`, `=` ou `recebe`.
- Nao penalize ausencia de declaracao formal de variaveis quando o uso das variaveis estiver claro.
- Nao aceite como resposta completa textos em linguagem natural que apenas descrevem a solucao sem apresentar passos algoritmicos executaveis mentalmente.
- Penalize respostas vagas, incompletas ou que nao deixam claro como percorrer dados, aplicar condicoes ou produzir a saida.

Teste manual no Moodle:
- criar questao com linguagem `Portugol`;
- conferir nome no seletor como `Portugol`;
- conferir descricao/orientacao exibida para aluno;
- responder com exemplo formal e confirmar avaliacao adequada;
- responder com exemplo semi-formal e confirmar aceitacao quando a logica estiver clara;
- responder com texto puramente descritivo e confirmar penalizacao;
- validar feedback em tentativa normal, feedback imediato/interativo e revisao.

Teste automatizado quando possivel:
- PHPUnit para `language_helper::get_options`, `normalise` e fallback;
- PHPUnit para prompt de Portugol em `grading_helper`;
- teste com provider fake garantindo que a instrucao especifica entra no prompt;
- teste de renderizacao para linguagem `Portugol`.

Decisoes a registrar se ficarem fora de escopo:
- se Portugol tera highlight proprio ou fallback visual inicial;
- se a descricao sera exibida apenas no form/preview ou tambem na tentativa;
- se a rubrica especifica de Portugol sera sempre injetada ou configuravel pelo professor;
- se Portugol deve aceitar acentos nos comandos ou normalizar variantes sem acento.

### Marco 9 - Admin, privacy, backup/restore e testes finais
Arquivos principais:
- `question/type/codejudge/classes/privacy/provider.php`
- `question/type/codejudge/db/access.php`
- `question/type/codejudge/backup/moodle2/*`
- `question/type/codejudge/tests/*`
- arquivos de versionamento/upgrade quando houver mudancas de schema.

Comportamento esperado:
- dados pessoais armazenados sao declarados e tratados pelo privacy API;
- backup/restore preserva configuracoes da questao;
- pacote final nao depende conceitualmente de `mod_iajudge` como container.

Teste manual no Moodle:
- executar backup e restore de Quiz com questao `codejudge`;
- revisar exportacao/delecao privacy quando aplicavel;
- validar instalacao em ambiente limpo.

Teste automatizado quando possivel:
- PHPUnit privacy provider;
- testes de backup/restore;
- testes de upgrade;
- testes de integracao do fluxo completo.

Decisoes a registrar se ficarem fora de escopo:
- se resultados de IA devem ou nao entrar em backup;
- destino final do `mod_iajudge` antigo: remover, manter separado ou usar apenas como legado.

## Bloqueadores reais
- Question engine: ainda nao ha integracao robusta entre resposta, task de IA, estado da questao e nota.
- Nota: `score` da autocorrecao alimenta a nota da questao via `manual_grade`.
- Revisao: comentario da autocorrecao aparece no comentario manual do question engine.
- Gradebook: recalculo do Quiz/gradebook foi validado apos a task.
- Feedback imediato: suporte assincrono inicial existe; falta validar `immediatefeedback`/`interactive` no Moodle e decidir como tratar steps intermediarios ainda ativos.
- Privacy: provider implementado para metadados, exportacao e delecao; falta validacao real no Moodle.
- Idempotencia: o disparo frontend foi removido; falta garantir deduplicacao no gatilho backend.
- Validacao real: ainda falta rodar em Moodle para confirmar instalacao, question bank, tentativa, revisao e backup/restore.
- Escopo legado: `mod_iajudge` ainda existe no repositorio e precisa de decisao explicita para nao confundir o objetivo do `qtype_codejudge`.

## Checklist detalhado

### 1. Estrutura do question type
- [x] Criar a raiz do plugin.
- [x] Adicionar `version.php`.
- [x] Adicionar `db/install.xml`.
- [x] Adicionar e corrigir `db/upgrade.php`.
- [x] Adicionar `lang/en/qtype_codejudge.php`.
- [x] Adicionar `lang/pt_br/qtype_codejudge.php`.
- [x] Adicionar `questiontype.php`.
- [x] Adicionar `question.php`.
- [x] Adicionar `edit_codejudge_form.php`.
- [x] Adicionar `renderer.php`.
- [x] Adicionar `pix/icon.svg`.
- [x] Adicionar `classes/local/language_helper.php`.
- [x] Adicionar `amd/src/editor.js`.
- [x] Adicionar `amd/build/editor.min.js`.
- [~] Adicionar `classes/` para apoio ao editor, correcao e formatacao.
- [~] Adicionar `tests/` com cobertura inicial do question type.
- [x] Normalizar defaults de persistencia para linguagem e altura do editor.

### 2. Integracao com o Quiz
- [ ] Garantir que o `codejudge` possa ser adicionado a um Quiz normalmente.
- [ ] Garantir que a tentativa do Quiz carregue a questao sem erros.
- [x] Garantir que salvar, retomar e revisar a tentativa funcione.
- [x] Garantir que a nota da questao entre no total da tentativa.
- [x] Garantir que o comentario da autocorrecao seja mostrado na revisao.
- [ ] Validar navegacao entre questoes com `codejudge`.
- [x] Validar comportamento em uma tentativa com multiplas questoes `codejudge`.
- [ ] Validar comportamento em quizzes mistos.
- [ ] Garantir que nao exista dependencia de um container separado para responder a questao.

### 3. Editor de codigo
- [x] Definir uma base de editor leve e independente de bibliotecas externas.
- [x] Suportar multiplas instancias na mesma pagina em codigo.
- [x] Suportar linguagem por questao.
- [x] Sincronizar valor do editor com o campo enviado ao Moodle em codigo.
- [x] Tratar `readonly` quando a questao estiver em revisao.
- [~] Validar que o editor nao bloqueia edicao/submissao em todos os navegadores e no Quiz real: interceptacao de submit removida, reteste pendente.
- [~] Revisar se o enfileiramento IA dentro do AMD deve continuar ou ser movido para ponto mais robusto.

### 3.1 Modernizacao do editor de codigo
- [x] Comparar Monaco, CodeMirror 6 e alternativas open source com foco em Moodle AMD, tamanho do pacote, licenca e manutencao.
- [x] Escolher editor e registrar decisao tecnica no checklist.
- [x] Adicionar assets locais do editor escolhido ao plugin, sem CDN.
- [x] Criar wrapper AMD mantendo a API atual de inicializacao do renderer.
- [x] Sincronizar editor moderno com o campo submetido pelo Moodle.
- [x] Preservar fallback para `textarea` quando o editor nao carregar.
- [x] Mapear linguagem da questao para highlight/editor mode.
- [~] Validar multiplas instancias em preview, tentativa e revisao.
- [ ] Validar fluxo de Quiz com navegacao, salvamento, retomada, finalizacao e revisao.
- [x] Atualizar pacote ZIP e instrucoes de teste apos a mudanca.

### 4. Correcao por IA
- [x] Definir gatilho backend para correcao apos finalizacao/submissao da tentativa.
- [~] Enfileirar a resposta do aluno para analise em codigo: helper/task existem, falta ligar ao ciclo backend correto.
- [x] Montar prompt com enunciado, linguagem, codigo e rubrica.
- [x] Persistir score, feedback e payload bruto da IA.
- [x] Definir tabela para feedback da IA.
- [~] Definir status por tentativa e por questao.
- [x] Garantir idempotencia em reprocessamentos.
- [x] Nao interceptar submit/navegacao do Quiz no frontend.

### 4.1 Revisao, nota e gradebook
- [x] Conectar score da IA ao grade do question engine por comentario/nota manual aplicada pela task.
- [x] Garantir que a nota final do Quiz reflita o resultado da questao.
- [x] Garantir recalculo quando houver nova tentativa.

### 5. Banco de questoes e Moodle
- [x] Confirmar que `codejudge` aparece como tipo de questao no banco.
- [x] Confirmar que criacao e edicao usam fluxo padrao de question type.
- [ ] Confirmar reutilizacao da mesma questao em varios quizzes.
- [~] Cobrir exportacao/importacao do tipo de questao: backup/restore de opcoes existe, falta validacao.
- [~] Cobrir restauracao de quizzes com questoes `codejudge`: codigo existe, falta teste real.
- [ ] Revisar `classes/privacy/provider.php`.
- [~] Revisar `db/access.php`.
- [ ] Garantir permissao correta para criar, editar, responder e revisar.

### 6. Testes
- [ ] Persistencia da questao.
- [x] Validacao basica de linguagem.
- [ ] Validacao de rubrica e nota.
- [x] Serializacao/resumo/comparacao basica da resposta.
- [ ] Adicionar `codejudge` a um Quiz.
- [ ] Iniciar tentativa com questao `codejudge`.
- [x] Salvar resposta com editor de codigo.
- [ ] Processar correcao por IA.
- [x] Revisar nota e comentario da autocorrecao.
- [ ] Verificar o editor de codigo na questao.
- [ ] Verificar o comportamento em revisao.
- [x] Verificar a exibicao da nota e do comentario da autocorrecao.

### 7. Limpeza de escopo
- [ ] Garantir que o plugin fique orientado apenas ao `codejudge` dentro do Quiz.
- [ ] Remover ou isolar qualquer dependencia conceitual de atividade container separada.
- [ ] Registrar decisao sobre manutencao, remocao ou legado do `mod_iajudge`.

## Criterio de pronto final
- [ ] `codejudge` funciona como tipo de questao do Moodle.
- [ ] `codejudge` pode ser usado em quizzes.
- [ ] O aluno responde com editor de codigo.
- [ ] A resposta recebe correcao por IA em fluxo rastreavel e idempotente.
- [x] A nota entra no Quiz e no gradebook.
- [x] O comentario da autocorrecao aparece na revisao.
- [ ] Privacy provider cobre os dados realmente armazenados.
- [ ] Backup/restore foi validado ou teve escopo explicitamente documentado.
- [ ] O fluxo nao depende de uma atividade separada como contenedor.


