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
- [x] Helper de linguagens e editor inicial de codigo adicionados.
- [~] Editor de codigo existe, mas ainda precisa ser validado no fluxo real do Quiz.
- [~] Pipeline de IA existe em codigo, mas ainda nao esta ligada corretamente ao ciclo real de tentativa/revisao/nota.
- [~] Testes unitarios basicos existem, mas ainda faltam testes de persistencia, integracao com question engine e fluxo de Quiz.
- [~] Backup/restore de opcoes existe, mas ainda precisa ser validado em backup/restauracao real de quizzes.
- [ ] Integracao completa com o question engine do Moodle ainda precisa ser implementada/validada.
- [ ] Nota e feedback da IA ainda nao entram no fluxo normal de revisao e grade do Quiz.
- [ ] Privacy provider precisa ser corrigido, pois ha armazenamento de `userid` e codigo submetido.

## Status real por fase

### Fase 1 - Consolidar o question type
- [x] Raiz do plugin, versionamento, install/upgrade, strings, form, renderer e classe de questao existem.
- [x] Persistencia das opcoes especificas da questao existe.
- [~] Compatibilidade com question bank parece encaminhada pelo codigo, mas precisa validacao em Moodle instalado.
- [ ] Instalar/atualizar em Moodle limpo e confirmar criacao/edicao pelo banco de questoes.

### Fase 2 - Editor de codigo na questao
- [x] Renderer cria campo de codigo e acopla AMD `qtype_codejudge/editor`.
- [x] AMD suporta indentacao, resize, multiplas instancias e readonly basico.
- [~] O editor e leve e funcional em codigo, mas nao houve validacao em tentativa real, revisao, preview e navegadores suportados.
- [ ] Confirmar que o editor nao interfere no submit normal do Quiz.

### Fase 3 - Compatibilidade com o Quiz
- [~] `question.php` define dados esperados, completude, resumo e comparacao de resposta.
- [~] A questao usa comportamento `manualgraded` como ponte temporaria.
- [ ] Validar adicionar `codejudge` a um Quiz.
- [ ] Validar iniciar tentativa, salvar, retomar e revisar.
- [ ] Validar tentativa com multiplas questoes `codejudge` e quiz misto.

### Fase 4 - Correcao por IA
- [x] Tabela `qtype_codejudge_grading` existe para fila/resultados.
- [x] Helper monta prompt e enfileira task adhoc.
- [x] Task chama provider de IA e persiste `score`, `feedback`, `rawresponse` e erro.
- [x] Web services AJAX existem para enfileirar e consultar status.
- [~] O ponto de disparo atual via AMD no submit do formulario e fragil.
- [ ] Ligar a fila ao ciclo correto do question engine/tentativa.
- [ ] Garantir idempotencia e evitar duplicidade de fila em salvar/retomar/reenvio.

### Fase 5 - Revisao e gradebook
- [ ] Aplicar `score` da IA ao grade da questao.
- [ ] Exibir feedback da IA na revisao da tentativa.
- [ ] Atualizar/recalcular tentativa e gradebook quando a task terminar.
- [ ] Definir comportamento para reprocessamento, nova tentativa e erro da IA.

### Fase 6 - Admin e seguranca
- [x] Settings de provider/modelo/chave/base URL e teste de conexao existem.
- [~] `db/access.php` existe sem capabilities customizadas; precisa decisao/validacao.
- [ ] Corrigir privacy provider para declarar e exportar/deletar dados pessoais armazenados.
- [ ] Revisar permissoes dos web services, principalmente enfileiramento e consulta de status.

### Fase 7 - Qualidade
- [~] Testes unitarios basicos existem para linguagem, helper de prompt e resposta.
- [ ] Testes de persistencia de opcoes da questao.
- [ ] Testes de integracao com question engine.
- [ ] Testes de Quiz: tentativa, salvamento, revisao, nota e feedback.
- [ ] Validacao manual fim a fim em Moodle.

## Proximo marco
Marco ativo sugerido: **Marco 1 - estabilizar o `qtype_codejudge` instalavel e editavel no banco de questoes**.

Objetivo imediato:
- confirmar que o plugin instala/atualiza sem erro;
- confirmar que aparece no question bank;
- confirmar que uma questao pode ser criada, editada, salva e recarregada com suas opcoes;
- registrar qualquer ajuste minimo necessario antes de entrar no fluxo do Quiz.

Nao entrar ainda em nota/gradebook/revisao por IA antes de fechar esse marco, exceto se o erro impedir instalacao ou edicao basica.

## Ordem de execucao por marcos

### Marco 1 - Estabilizar o `qtype_codejudge` instalavel e editavel no banco de questoes
Arquivos principais:
- `question/type/codejudge/version.php`
- `question/type/codejudge/db/install.xml`
- `question/type/codejudge/db/upgrade.php`
- `question/type/codejudge/questiontype.php`
- `question/type/codejudge/edit_code_form.php`
- `question/type/codejudge/lang/*/qtype_codejudge.php`
- `question/type/codejudge/pix/*`

Comportamento esperado:
- o Moodle reconhece o plugin como tipo de questao;
- o professor cria e edita uma questao `codejudge`;
- linguagem, rubrica, codigo inicial e altura do editor sao persistidos;
- a questao reabre no form com os mesmos valores salvos.

Teste manual no Moodle:
- instalar/atualizar plugins;
- criar uma questao `codejudge` no question bank;
- salvar, editar novamente e conferir os campos;
- duplicar/reutilizar a questao, se aplicavel.

Teste automatizado quando possivel:
- PHPUnit para persistencia de opcoes em `questiontype.php`;
- PHPUnit para validacao de linguagem, rubrica e altura do editor.

Decisoes a registrar se ficarem fora de escopo:
- quais languages sao suportadas oficialmente neste marco;
- se capabilities customizadas ficam adiadas para o Marco 5.

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
- se o submit via AMD deve ser removido/desabilitado ate a IA ser integrada corretamente.

### Marco 3 - Ligar a fila IA ao ciclo correto da tentativa
Arquivos principais:
- `question/type/codejudge/question.php`
- `question/type/codejudge/classes/local/grading_helper.php`
- `question/type/codejudge/classes/task/grade_submission.php`
- `question/type/codejudge/classes/external/queue_grading.php`
- `question/type/codejudge/classes/external/check_status.php`
- `question/type/codejudge/db/services.php`
- `question/type/codejudge/db/install.xml`

Comportamento esperado:
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
- estado da questao reflete aguardando correcao, corrigida ou erro;
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

### Marco 5 - Gradebook, privacy, backup/restore e testes finais
Arquivos principais:
- `question/type/codejudge/classes/privacy/provider.php`
- `question/type/codejudge/db/access.php`
- `question/type/codejudge/backup/moodle2/*`
- `question/type/codejudge/tests/*`
- arquivos de versionamento/upgrade quando houver mudancas de schema.

Comportamento esperado:
- nota final do Quiz e gradebook refletem a nota da questao;
- dados pessoais armazenados sao declarados e tratados pelo privacy API;
- backup/restore preserva configuracoes da questao;
- pacote final nao depende conceitualmente de `mod_iajudge` como container.

Teste manual no Moodle:
- conferir gradebook apos correcao;
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
- Nota: `score` da IA e gravado em tabela propria, mas ainda nao alimenta a nota da questao.
- Revisao: feedback da IA ainda nao aparece como feedback normal da tentativa.
- Gradebook: sem nota aplicada ao question engine, o gradebook nao recebe o resultado final esperado.
- Privacy: o provider atual declara ausencia de dados pessoais, mas a tabela de grading armazena `userid` e codigo submetido.
- Idempotencia: o disparo por AMD no submit pode criar duplicidade ou fila fora do step correto.
- Validacao real: ainda falta rodar em Moodle para confirmar instalacao, question bank, tentativa, revisao e backup/restore.
- Escopo legado: `mod_iajudge` ainda existe no repositorio e precisa de decisao explicita para nao confundir o objetivo do `qtype_codejudge`.

## Checklist detalhado

### 1. Estrutura do question type
- [x] Criar a raiz do plugin.
- [x] Adicionar `version.php`.
- [x] Adicionar `db/install.xml`.
- [x] Adicionar `db/upgrade.php`.
- [x] Adicionar `lang/en/qtype_codejudge.php`.
- [x] Adicionar `lang/pt_br/qtype_codejudge.php`.
- [x] Adicionar `questiontype.php`.
- [x] Adicionar `question.php`.
- [x] Adicionar `edit_code_form.php`.
- [x] Adicionar `renderer.php`.
- [x] Adicionar `pix/icon.svg`.
- [x] Adicionar `classes/local/language_helper.php`.
- [x] Adicionar `amd/src/editor.js`.
- [x] Adicionar `amd/build/editor.min.js`.
- [~] Adicionar `classes/` para apoio ao editor, correcao e formatacao.
- [~] Adicionar `tests/` com cobertura inicial do question type.

### 2. Integracao com o Quiz
- [ ] Garantir que o `codejudge` possa ser adicionado a um Quiz normalmente.
- [ ] Garantir que a tentativa do Quiz carregue a questao sem erros.
- [ ] Garantir que salvar, retomar e revisar a tentativa funcione.
- [ ] Garantir que a nota da questao entre no total da tentativa.
- [ ] Garantir que o feedback seja mostrado na revisao.
- [ ] Validar navegacao entre questoes com `codejudge`.
- [ ] Validar comportamento em uma tentativa com multiplas questoes `codejudge`.
- [ ] Validar comportamento em quizzes mistos.
- [ ] Garantir que nao exista dependencia de um container separado para responder a questao.

### 3. Editor de codigo
- [x] Definir uma base de editor leve e independente de bibliotecas externas.
- [x] Suportar multiplas instancias na mesma pagina em codigo.
- [x] Suportar linguagem por questao.
- [x] Sincronizar valor do editor com o campo enviado ao Moodle em codigo.
- [x] Tratar `readonly` quando a questao estiver em revisao.
- [~] Validar que o editor nao bloqueia edicao/submissao em todos os navegadores e no Quiz real.
- [~] Revisar se o enfileiramento IA dentro do AMD deve continuar ou ser movido para ponto mais robusto.

### 4. Correcao por IA
- [~] Definir o ponto de disparo da correcao.
- [x] Enfileirar a resposta do aluno para analise em codigo.
- [x] Montar prompt com enunciado, linguagem, codigo e rubrica.
- [x] Persistir score, feedback e payload bruto da IA.
- [ ] Atualizar o estado da questao na tentativa quando a IA responder.
- [x] Definir tabela para feedback da IA.
- [~] Definir status por tentativa e por questao.
- [ ] Garantir idempotencia em reprocessamentos.
- [ ] Conectar score da IA ao grade do question engine.
- [ ] Garantir que a nota final do Quiz reflita o resultado da questao.
- [ ] Garantir recalculo quando houver nova tentativa.

### 5. Banco de questoes e Moodle
- [~] Confirmar que `codejudge` aparece como tipo de questao no banco: previsto em codigo, falta validacao real.
- [~] Confirmar que criacao e edicao usam fluxo padrao de question type: previsto em codigo, falta validacao real.
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
- [ ] Salvar resposta com editor de codigo.
- [ ] Processar correcao por IA.
- [ ] Revisar nota e feedback.
- [ ] Verificar o editor de codigo na questao.
- [ ] Verificar o comportamento em revisao.
- [ ] Verificar a exibicao da nota e do feedback.

### 7. Limpeza de escopo
- [ ] Garantir que o plugin fique orientado apenas ao `codejudge` dentro do Quiz.
- [ ] Remover ou isolar qualquer dependencia conceitual de atividade container separada.
- [ ] Registrar decisao sobre manutencao, remocao ou legado do `mod_iajudge`.

## Criterio de pronto final
- [ ] `codejudge` funciona como tipo de questao do Moodle.
- [ ] `codejudge` pode ser usado em quizzes.
- [ ] O aluno responde com editor de codigo.
- [ ] A resposta recebe correcao por IA em fluxo rastreavel e idempotente.
- [ ] A nota entra no Quiz e no gradebook.
- [ ] O feedback aparece na revisao.
- [ ] Privacy provider cobre os dados realmente armazenados.
- [ ] Backup/restore foi validado ou teve escopo explicitamente documentado.
- [ ] O fluxo nao depende de uma atividade separada como contenedor.
