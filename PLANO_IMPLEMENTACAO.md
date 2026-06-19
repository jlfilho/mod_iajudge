# Plano de Implementacao - `codejudge` no ecossistema do Quiz

## Objetivo
Transformar `codejudge` em um tipo de questao do Moodle que funcione dentro do `Quiz` e do question bank, mantendo:
- editor de codigo na questao;
- correcao por IA de forma assincrona;
- nota e feedback integrados ao fluxo normal do Quiz.

## Diretriz de Arquitetura
- O Quiz continua sendo o container da atividade no curso.
- `codejudge` passa a ser um `qtype_*` do question bank.
- O comportamento especial fica no proprio question type e em servicos auxiliares.
- O core do Quiz nao deve ser alterado sem necessidade tecnica real.
- O fluxo deve respeitar edicao, tentativa, revisao, nota e feedback do Moodle.

## Alcance Funcional
### O que `codejudge` precisa oferecer
- Enunciado da questao.
- Linguagem permitida.
- Editor de codigo embutido.
- Nota maxima configuravel pelo professor.
- Rubrica ou criterio de avaliacao.
- Resposta livre em codigo.
- Correcao por IA de forma assincrona.
- Feedback e nota visiveis na revisao.

### O que o Quiz continua fazendo
- Selecionar questoes do question bank.
- Montar tentativas com slots.
- Controlar navegacao, tentativas e revisao.
- Publicar nota consolidada no gradebook.

## Fases de Implementacao
### Fase 1 - Consolidar o question type
- Validar o formato padrao do `qtype_codejudge`.
- Revisar persistencia da questao e das opcoes.
- Garantir campos de linguagem, nota, rubrica e codigo inicial.
- Ajustar strings, validacoes e estrutura de instalacao.

### Fase 2 - Editor de codigo na questao
- Integrar um editor de codigo funcional na renderizacao.
- Garantir suporte a multiplas instancias na mesma pagina.
- Sincronizar editor visual com o valor enviado ao question engine.
- Tratar modo de revisao e modo readonly.

### Fase 3 - Compatibilidade com o Quiz
- Garantir que a questao possa ser adicionada a um Quiz normalmente.
- Validar comportamento em tentativa, salvamento, revisao e reabertura.
- Garantir que o Quiz carregue e revise a questao sem modificar o core.
- Confirmar que a nota da questao entra na pontuacao da tentativa.

### Fase 4 - Pipeline backend de correcao por IA
- Criar a pipeline assincrona de correcao sem interceptar o submit ou a navegacao do Quiz no frontend.
- Definir um gatilho backend para correcao apos a tentativa ser finalizada/submetida, ou outro ponto seguro do ciclo do question engine.
- Localizar respostas `codejudge` dentro da tentativa do Quiz.
- Enfileirar uma unica correcao por questao, tentativa e resposta.
- Enviar para a IA o enunciado, a linguagem, o codigo e a rubrica.
- Persistir status, score, feedback estruturado, erro e resposta bruta da IA.
- Garantir idempotencia para evitar duplicidade em refresh, retomar tentativa, reenvio ou reprocessamento.

### Fase 5 - Revisao e gradebook
- Aplicar o score da IA ao question engine via comportamento `manualgraded`.
- Exibir feedback da IA na revisao como comentario manual do question engine.
- Representar estado aguardando correcao, corrigida ou erro de forma coerente para professor e aluno.
- Garantir que a nota da questao alimente a nota do Quiz.
- Atualizar/recalcular tentativa e gradebook quando a correcao terminar.
- Validar em Moodle instalado o recalculo de tentativa, revisao e gradebook.
- Tratar reprocessamento manual e timeout como pendencias explicitas apos a validacao do fluxo automatico.

### Fase 6 - Modernizacao do editor de codigo
- Avaliar editor open source adequado ao Moodle, como Monaco, CodeMirror 6 ou alternativa equivalente.
- Definir estrategia de empacotamento sem depender de CDN em producao.
- Integrar o novo editor ao AMD do `qtype_codejudge` mantendo sincronizacao com o campo enviado ao question engine.
- Garantir suporte a multiplas instancias, readonly/revisao, acessibilidade, tema e linguagem por questao.
- Manter fallback seguro para `textarea` caso o editor moderno nao carregue.
- Validar que o novo editor nao intercepta submit, navegacao, salvamento, retomada ou revisao do Quiz.

### Fase 7 - Feedback imediato e multiplas tentativas
- Substituir a ponte fixa `manualgraded` por uma estrategia que respeite o comportamento preferido do Quiz quando for `immediatefeedback` ou `interactive`.
- Definir como o `qtype_codejudge` captura cada acao de verificacao da questao durante uma tentativa aberta.
- Enfileirar ou iniciar correcao por step, resposta e tentativa, preservando idempotencia por tentativa interativa.
- Definir UX para latencia da IA: aguardando correcao, atualizacao posterior, bloqueio temporario, polling ou fallback para correcao apos submissao.
- Atualizar feedback, score e estado da questao no question engine para cada tentativa/check suportado.
- Garantir que multiplas tentativas da mesma questao nao sobrescrevam feedback incorreto nem dupliquem fila.
- Validar compatibilidade com Quiz em feedback imediato, modo interativo com multiplas tentativas, revisao e gradebook.

### Fase 8 - Portugol para algoritmos iniciantes
- Status: implementada em codigo; pendente validacao em Moodle instalado com uma questao real de algoritmo iniciante.
- Adicionar `Portugol` como linguagem selecionavel para questoes voltadas a aprendizagem de algoritmos.
- Descrever Portugol como algoritmo estruturado com comandos como `leia`, `escreva`, `se`, `senao`, `enquanto`, `para`, variaveis e atribuicoes.
- Ajustar o prompt da IA para avaliar Portugol como pseudocodigo formal estruturado, nao como texto descritivo livre.
- Exigir entrada, processamento e saida claros, com variaveis, atribuicoes, condicoes ou repeticoes quando o problema pedir.
- Aceitar variacoes comuns de sintaxe, como `leia`, `ler`, `entrada`, `escreva`, `imprimir`, `mostrar`, `se`, `entao`, `senao`, `fimse`, `para`, `enquanto`, `faca`, `fimpara` e `fimenquanto`.
- Aceitar atribuicoes com `<-`, seta visual, `=` ou `recebe`.
- Penalizar respostas vagas em linguagem natural que apenas descrevem a ideia sem apresentar passos algoritmicos executaveis mentalmente.
- Validar editor, highlight/fallback, prompt, rubrica e feedback com questoes de algoritmos para iniciantes.

### Fase 9 - Admin e seguranca
- Revisar capabilities do question type.
- Garantir que apenas usuarios autorizados criem e editem questoes.
- Revisar privacy provider.
- Preparar backup e restore da questao.

### Fase 10 - Qualidade
- Criar testes unitarios para persistencia e validacao.
- Criar testes de integracao com o question engine.
- Testar renderizacao do editor, tentativas e revisao.
- Validar fluxo fim a fim com um Quiz contendo questoes `codejudge`.

## Arquivos Principais a Ajustar
### No question type
- `question/type/codejudge/version.php`
- `question/type/codejudge/db/install.xml`
- `question/type/codejudge/db/upgrade.php`
- `question/type/codejudge/questiontype.php`
- `question/type/codejudge/question.php`
- `question/type/codejudge/edit_codejudge_form.php`
- `question/type/codejudge/renderer.php`
- `question/type/codejudge/lang/en/qtype_codejudge.php`
- `question/type/codejudge/lang/pt_br/qtype_codejudge.php`
- `question/type/codejudge/classes/*`
- `question/type/codejudge/amd/*`
- `question/type/codejudge/tests/*`
- `question/type/codejudge/pix/*`

## Decisao de Escopo
- O objetivo final e fazer `codejudge` viver dentro do ecossistema do Quiz.
- Todo o fluxo de usuario deve ser construido a partir do question type e dos servicos associados a ele.
- Nao ha dependencia arquitetural desejada de um modulo de atividade separado.

## Criterio de Pronto
- Uma questao `codejudge` pode ser criada no question bank.
- Um Quiz pode conter questoes `codejudge` sem alterar o core do Quiz.
- O aluno ve um editor de codigo funcional dentro da questao.
- A resposta e enviada e corrigida por IA de forma assincrona.
- A nota e o feedback aparecem na revisao da tentativa.
- O gradebook recebe a nota da tentativa normalmente.
