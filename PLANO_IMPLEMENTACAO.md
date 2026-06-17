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

### Fase 4 - Correcao por IA
- Criar a pipeline assincrona de correcao.
- Enviar para a IA o enunciado, a linguagem, o codigo e a rubrica.
- Persistir score, feedback estruturado e resposta bruta da IA.
- Atualizar o estado da tentativa quando a correcao terminar.

### Fase 5 - Revisao e gradebook
- Exibir feedback da IA na revisao.
- Garantir que a nota da questao alimente a nota do Quiz.
- Confirmar atualizacao do gradebook quando a correcao terminar.
- Tratar reprocessamento e novas tentativas conforme a regra do Quiz.

### Fase 6 - Admin e seguranca
- Revisar capabilities do question type.
- Garantir que apenas usuarios autorizados criem e editem questoes.
- Revisar privacy provider.
- Preparar backup e restore da questao.

### Fase 7 - Qualidade
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
- `question/type/codejudge/edit_code_form.php`
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
