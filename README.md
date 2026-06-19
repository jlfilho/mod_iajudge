# qtype_codejudge

`qtype_codejudge` Ã© um tipo de questÃ£o do Moodle para uso no Banco de questÃµes e no Quiz. Ele permite criar questÃµes de programaÃ§Ã£o com editor de cÃ³digo, correÃ§Ã£o automÃ¡tica assistida por IA, nota aplicada ao question engine e comentÃ¡rio de revisÃ£o no fluxo normal do Moodle.

O plugin foi desenhado para funcionar como `question/type/codejudge`, sem alterar o core do Quiz e sem depender de uma atividade separada como contÃªiner.

## Estado atual

Implementado e validado em fluxo real:

- InstalaÃ§Ã£o como tipo de questÃ£o `qtype_codejudge`.
- CriaÃ§Ã£o, ediÃ§Ã£o, salvamento e reabertura pelo Banco de questÃµes.
- Uso em Quiz com tentativa, salvamento, retomada, finalizaÃ§Ã£o e revisÃ£o.
- Editor de cÃ³digo com CodeMirror 6 empacotado localmente.
- CorreÃ§Ã£o por IA disparada no backend apÃ³s submissÃ£o/finalizaÃ§Ã£o da tentativa.
- Fila assÃ­ncrona via task ad hoc do Moodle.
- IdempotÃªncia para evitar duplicidade de correÃ§Ã£o por questÃ£o, tentativa e resposta.
- Compatibilidade inicial com Quiz em feedback imediato por resultado provisÃ³rio `needsgrading`.
- Nota aplicada ao question engine por comentÃ¡rio/nota manual gerada pela task assÃ­ncrona.
- Recalculo da tentativa, nota final do Quiz e gradebook.
- ComentÃ¡rio da autocorreÃ§Ã£o exibido na revisÃ£o do Moodle.

Ainda pendente ou em validaÃ§Ã£o:

- Validacao real de exportacao/delecao pelo privacy API em Moodle instalado.
- ValidaÃ§Ã£o real de backup/restore em Quiz restaurado.
- PolÃ­tica completa para reprocessamento manual, timeout e erro persistente da IA.
- Testes automatizados mais amplos de integraÃ§Ã£o com Quiz/question engine.

## Requisitos

- Moodle 5.2 ou superior.
- PHP e banco de dados compatÃ­veis com a versÃ£o do Moodle.
- Cron do Moodle ativo.
- PermissÃ£o administrativa para instalar tipos de questÃ£o.
- Um provedor de IA configurado, salvo se o objetivo for apenas testar criaÃ§Ã£o/ediÃ§Ã£o e submissÃ£o sem correÃ§Ã£o.

O plugin usa tasks ad hoc do Moodle. Sem cron, a tentativa pode ser finalizada, mas a correÃ§Ã£o automÃ¡tica nÃ£o serÃ¡ processada.

## InstalaÃ§Ã£o

### InstalaÃ§Ã£o por ZIP

Use o pacote gerado em:

```text
dist/qtype_codejudge_moodle_v18.zip
```

O ZIP deve ter esta estrutura interna:

```text
codejudge/version.php
codejudge/questiontype.php
codejudge/renderer.php
codejudge/amd/build/editor.min.js
codejudge/amd/build/codemirror6.min.js
```

No Moodle:

1. Acesse AdministraÃ§Ã£o do site.
2. VÃ¡ em Plugins > Instalar plugins.
3. Envie o ZIP.
4. Quando solicitado, confirme que o tipo Ã© `question/type`.
5. Finalize a atualizaÃ§Ã£o do banco.
6. Limpe os caches do Moodle.

### InstalaÃ§Ã£o manual

Copie a pasta do plugin para:

```text
<moodle_root>/question/type/codejudge
```

Depois execute a atualizaÃ§Ã£o:

```bash
php admin/cli/upgrade.php
php admin/cli/purge_caches.php
```

Em instalaÃ§Ãµes Moodle 5.2 com diretÃ³rio `public`, confirme qual Ã© o `dirroot` real configurado no container. O plugin precisa estar no mesmo filesystem usado pelo processo web e pelo cron.

## AtualizaÃ§Ã£o

Ao atualizar uma instalaÃ§Ã£o existente:

1. Substitua a pasta `question/type/codejudge`.
2. Rode o upgrade do Moodle.
3. Limpe caches.
4. Confirme que a versao exibida e `0.1.23` ou superior.

Comandos tÃ­picos:

```bash
php admin/cli/upgrade.php
php admin/cli/purge_caches.php
```

Se web e cron estiverem em containers diferentes, ambos precisam ter o cÃ³digo atualizado do plugin. Caso contrÃ¡rio, o cron pode falhar com erro de classe invÃ¡lida para `\qtype_codejudge\task\grade_submission`.

## ConfiguraÃ§Ã£o da IA

Acesse:

```text
AdministraÃ§Ã£o do site > Plugins > Tipos de questÃ£o > Coding question
```

ConfiguraÃ§Ãµes disponÃ­veis:

- Provedor de IA: Moodle core AI, OpenAI, Anthropic, Google Gemini ou Ollama.
- Chave de API.
- Base URL, Ãºtil para Ollama, proxies ou gateways corporativos.
- Nome do modelo.
- BotÃ£o de teste de conexÃ£o.

Provedores suportados no cÃ³digo:

- `core_ai`
- `openai`
- `anthropic`
- `gemini`
- `ollama`

Exemplos de modelos:

- OpenAI: `gpt-4o`
- Anthropic: `claude-3-5-sonnet-20241022`
- Gemini: `gemini-1.5-pro`
- Ollama: `llama3`

O feedback Ã© solicitado ao provedor no idioma do usuÃ¡rio do Moodle. Literais do problema, como saÃ­das esperadas `VALIDA` ou `INVALIDA`, podem permanecer no idioma do enunciado porque fazem parte da especificaÃ§Ã£o da questÃ£o.

## Como criar uma questÃ£o

No Banco de questÃµes:

1. Clique em Criar nova questÃ£o.
2. Selecione `Coding question`.
3. Preencha o enunciado.
4. Selecione a linguagem permitida.
5. Informe a rubrica de correÃ§Ã£o.
6. Opcionalmente informe cÃ³digo inicial.
7. Ajuste a altura do editor, se necessÃ¡rio.
8. Salve.

Campos especÃ­ficos:

- Linguagem.
- Rubrica de correÃ§Ã£o.
- CÃ³digo inicial.
- Altura do editor.

A rubrica Ã© usada para montar o prompt da correÃ§Ã£o, mas nÃ£o Ã© exibida ao aluno na tentativa ou no preview.

## Linguagens suportadas

Atualmente o plugin aceita:

- Python.
- C.
- Java.
- JavaScript.
- Portugol.


A correcao por IA aplica uma regra geral de conformidade de linguagem: quando a questao exigir Python, C, Java, JavaScript ou Portugol, uma resposta escrita principalmente em outra linguagem nao deve receber nota alta apenas por resolver o problema. Se a logica estiver correta mas a linguagem estiver errada, a instrucao interna limita a nota a no maximo 30; se a resposta nao puder ser interpretada como a linguagem esperada, limita a no maximo 20.
O CodeMirror 6 usa highlight de sintaxe correspondente a essas linguagens. C Ã© renderizado pelo modo C/C++. Portugol usa um modo leve prÃ³prio para destacar comandos comuns de algoritmos estruturados.

### Portugol

Use Portugol para escrever um algoritmo estruturado, com comandos como `leia`, `escreva`, `se`, `senÃ£o`, `enquanto`, `para`, variÃ¡veis e atribuiÃ§Ãµes.

Portugol Ã© tratado como algoritmo formal estruturado, nÃ£o como texto livre. A correÃ§Ã£o por IA recebe uma orientaÃ§Ã£o especÃ­fica para exigir entrada, processamento e saÃ­da claros. Respostas que apenas descrevem a ideia, como "contar as vogais e mostrar o resultado", nÃ£o devem ser aceitas como soluÃ§Ã£o completa.

Uma resposta em Portugol deve ter, no mÃ­nimo:

- declaraÃ§Ã£o ou uso claro de variÃ¡veis;
- comando de entrada, como `leia(...)`;
- processamento com atribuiÃ§Ã£o, condiÃ§Ã£o ou repetiÃ§Ã£o;
- comando de saÃ­da, como `escreva(...)`;
- estrutura lÃ³gica suficiente para resolver todos os casos do problema.

Quando a questao exigir Portugol, uma resposta escrita principalmente em Python, C, JavaScript, Java ou outra linguagem executavel nao deve receber nota alta apenas por resolver o problema. A instrucao interna da IA limita esse caso a nota baixa, tratando como nao conformidade de linguagem.

## Editor de cÃ³digo

O editor visual usa CodeMirror 6 empacotado localmente no plugin:

```text
amd/build/codemirror6.min.js
```

NÃ£o hÃ¡ dependÃªncia de CDN em runtime.

Recursos incluÃ­dos:

- NumeraÃ§Ã£o de linhas.
- Syntax highlighting.
- IndentaÃ§Ã£o com Tab.
- Undo/redo.
- Bracket matching.
- Fold gutter.
- Linha ativa.
- Autocomplete bÃ¡sico.
- Modo readonly para preview/revisÃ£o quando aplicÃ¡vel.

O `textarea` original do Moodle continua existindo e Ã© o campo real enviado ao question engine. O CodeMirror apenas sincroniza o valor com esse campo. Se o CodeMirror falhar ao carregar, o `textarea` permanece utilizÃ¡vel como fallback.

## Uso no Quiz

Depois de criada, a questÃ£o pode ser adicionada normalmente a um Quiz.

Fluxo esperado:

1. O aluno inicia a tentativa.
2. O aluno escreve o cÃ³digo no editor.
3. O Moodle salva a resposta como parte da tentativa.
4. Ao finalizar/submeter a tentativa, o observer backend localiza as respostas `codejudge`.
5. O plugin enfileira uma task ad hoc por questÃ£o, tentativa e resposta.
6. O cron processa a task.
7. A IA retorna score e feedback.
8. O plugin aplica a nota ao question engine.
9. O Quiz recalcula tentativa, nota final e gradebook.
10. O comentÃ¡rio da autocorreÃ§Ã£o aparece na revisÃ£o.

O frontend nÃ£o intercepta submit nem navegaÃ§Ã£o do Quiz. A correÃ§Ã£o Ã© disparada no backend.

## Feedback imediato e modo interativo

O plugin implementa suporte inicial para quizzes configurados com `immediatefeedback` ou `interactive`.

Como a IA Ã© assÃ­ncrona, o `codejudge` nÃ£o consegue devolver a nota real no mesmo ciclo sÃ­ncrono do botÃ£o de verificar. Em vez disso:

1. A questÃ£o aceita o comportamento automÃ¡tico solicitado pelo Moodle.
2. O mÃ©todo de avaliaÃ§Ã£o sÃ­ncrona retorna estado provisÃ³rio `needsgrading`.
3. O step mais recente da resposta Ã© enfileirado para correÃ§Ã£o por IA.
4. Durante uma tentativa ainda aberta, a task grava score e comentario, mas nao chama `manual_grade()`.
5. A aplicacao da nota ao question engine fica para depois da submissao final da tentativa, evitando erro de sequencia ao navegar entre paginas.
6. Se uma resposta mais antiga terminar depois de uma resposta nova, o resultado antigo nao sobrescreve o step mais recente.
7. Quando o behaviour for `immediatefeedback` ou `interactive`, o comentario da autocorrecao e exibido inline na propria questao assim que estiver disponivel.

Status atual:

- `immediatefeedback`: suporte em cÃ³digo, pendente de validaÃ§Ã£o real no Moodle.
- `interactive` com mÃºltiplas tentativas: suporte parcial. O plugin enfileira por step e protege contra resultados atrasados, mas steps intermediÃ¡rios ainda ativos podem nÃ£o receber nota atÃ© que estejam em estado aplicÃ¡vel pelo question engine.
- ComentÃ¡rio formativo inline: implementado para `immediatefeedback` e `interactive`, pendente de validaÃ§Ã£o real no Moodle.
- Polling automÃ¡tico, bloqueio temporÃ¡rio do botÃ£o e polÃ­tica final de timeout ainda nÃ£o estÃ£o fechados.

## Cron

O cron do Moodle precisa executar as tasks ad hoc.

Exemplo:

```bash
php admin/cli/cron.php -f
```

Em Docker, execute no container que contÃ©m o mesmo cÃ³digo Moodle usado pelo plugin:

```bash
docker compose exec moodle php /var/www/moodle/admin/cli/cron.php -f
```

O caminho pode variar conforme a imagem. Confirme com:

```bash
find /var/www -path '*/admin/cli/cron.php' -print
```

Se o Moodle estiver usando `gosu` ou usuÃ¡rio `www-data`, adapte:

```bash
docker compose exec moodle gosu www-data php /var/www/moodle/admin/cli/cron.php -f
```

## Dados persistidos

O plugin usa principalmente duas tabelas:

- `qtype_codejudge_options`: opÃ§Ãµes da questÃ£o.
- `qtype_codejudge_grading`: fila e resultados da correÃ§Ã£o.

`qtype_codejudge_grading` pode armazenar:

- ID da questÃ£o.
- ID da tentativa da questÃ£o.
- ID do usuÃ¡rio.
- Linguagem.
- CÃ³digo submetido.
- Rubrica.
- Prompt enviado ao provedor.
- Status.
- Score.
- Feedback.
- Resposta bruta do provedor.
- Mensagem de erro.
- Dados de aplicaÃ§Ã£o da nota ao question engine.

O privacy provider declara esses dados, exporta registros por usuario/contexto de Quiz e remove registros quando o Moodle executa delecao aprovada de dados pessoais.

## Backup e restore

Existe implementaÃ§Ã£o inicial para backup/restore das opÃ§Ãµes da questÃ£o:

```text
backup/moodle2/backup_qtype_codejudge_plugin.class.php
backup/moodle2/restore_qtype_codejudge_plugin.class.php
```

Status atual:

- CÃ³digo de backup/restore das opÃ§Ãµes existe.
- Ainda falta validaÃ§Ã£o real de backup e restauraÃ§Ã£o de Quiz contendo questÃµes `codejudge`.
- A decisÃ£o sobre incluir ou nÃ£o resultados da IA no backup ainda precisa ser registrada.

## Build do CodeMirror 6

Os assets do CodeMirror 6 sÃ£o gerados a partir de:

```text
amd/src/codemirror6_entry.js
tools/build-codemirror6.mjs
```

Para reconstruir:

```bash
npm install
npm run build:codemirror6
```

Arquivos gerados:

```text
amd/src/codemirror6.js
amd/build/codemirror6.min.js
```

Depois de alterar `amd/src/editor.js`, regenere tambÃ©m:

```bash
npx esbuild question/type/codejudge/amd/src/editor.js --minify --outfile=question/type/codejudge/amd/build/editor.min.js
```

## Empacotamento

O pacote instalÃ¡vel precisa ter `codejudge/` como pasta raiz.

Exemplo de empacotamento com Python a partir da raiz do repositÃ³rio:

```bash
python - <<'PY'
import os
import zipfile
from pathlib import Path

root = Path('question/type/codejudge')
outdir = Path('dist')
outdir.mkdir(exist_ok=True)
outfile = outdir / 'qtype_codejudge_moodle.zip'

with zipfile.ZipFile(outfile, 'w', zipfile.ZIP_DEFLATED) as zf:
    for dirpath, dirnames, filenames in os.walk(root):
        dirnames[:] = [d for d in dirnames if d not in {'.git', '__pycache__'}]
        rel_dir = Path(dirpath).relative_to(root)
        arc_dir = Path('codejudge') / rel_dir
        zf.writestr(str(arc_dir).replace('\\', '/') + '/', '')
        for filename in filenames:
            if filename.endswith(('.pyc', '.pyo')):
                continue
            path = Path(dirpath) / filename
            arc = Path('codejudge') / path.relative_to(root)
            zf.write(path, str(arc).replace('\\', '/'))

print(outfile)
PY
```

Antes de instalar o ZIP, confira se `codejudge/version.php` estÃ¡ na raiz interna do pacote.

## Testes manuais recomendados

### Banco de questÃµes

- Instalar ou atualizar o plugin.
- Criar uma questÃ£o `Coding question`.
- Salvar.
- Reabrir e confirmar linguagem, rubrica, cÃ³digo inicial e altura.
- Abrir preview e confirmar enunciado, editor e ausÃªncia da rubrica para aluno.

### Editor

- Digitar cÃ³digo.
- Usar Tab para indentaÃ§Ã£o.
- Testar undo/redo.
- Conferir highlight da linguagem selecionada.
- Conferir readonly na revisÃ£o.
- Confirmar fallback caso o bundle do CodeMirror seja bloqueado ou removido.

### Quiz

- Adicionar uma questÃ£o `codejudge` a um Quiz.
- Criar Quiz com mÃºltiplas questÃµes `codejudge`.
- Criar Quiz misto com outros tipos de questÃ£o.
- Iniciar tentativa.
- Navegar entre pÃ¡ginas.
- Salvar e retomar.
- Finalizar.
- Rodar cron.
- Conferir nota da questÃ£o.
- Conferir nota da tentativa e gradebook.
- Conferir comentÃ¡rio da autocorreÃ§Ã£o na revisÃ£o.

### Idioma

- Testar usuÃ¡rio em `pt_br`.
- Testar usuÃ¡rio em inglÃªs.
- Confirmar que os rÃ³tulos do Moodle usam o idioma do usuÃ¡rio.
- Confirmar que o feedback solicitado Ã  IA segue o idioma do usuÃ¡rio, preservando literais tÃ©cnicos do enunciado.

## Testes automatizados

HÃ¡ testes iniciais para:

- Helper de linguagens.
- Estrutura bÃ¡sica da questÃ£o.
- Helper de prompt/correÃ§Ã£o.

Cobertura adicional implementada:

- Persistencia de opcoes em `questiontype.php`.
- Contrato do privacy provider.
- Contrato de backup/restore das opcoes da questao.

Ainda faltam testes automatizados mais completos para:

- Fluxo real de aplicacao de nota no question engine.
- Observer de submissao do Quiz em ambiente integrado.
- Fluxo fim a fim com Quiz, preferencialmente via Behat.

## Problemas comuns

### Moodle nÃ£o reconhece o tipo de plugin no ZIP

Confirme que o ZIP tem esta estrutura:

```text
codejudge/version.php
```

NÃ£o empacote como:

```text
question/type/codejudge/version.php
```

### `version.php not found`

O ZIP provavelmente foi criado com uma pasta raiz incorreta. RefaÃ§a o pacote garantindo `codejudge/` como raiz.

### `core_plugin/corrupted_archive_structure`

Verifique se a pasta interna do ZIP se chama exatamente `codejudge` e se contÃ©m `version.php` diretamente dentro dela.

### Classe de task invÃ¡lida no cron

O cron estÃ¡ executando em um ambiente que nÃ£o tem o cÃ³digo do plugin atualizado.

Confirme:

```bash
find /var/www -path '*/question/type/codejudge/classes/task/grade_submission.php' -print
```

O container/processo do cron e o container/processo web precisam enxergar o mesmo cÃ³digo.

### A nota ainda aparece como "Requer avaliaÃ§Ã£o"

Verifique:

- Se o cron rodou.
- Se hÃ¡ registros `graded` em `qtype_codejudge_grading`.
- Se `gradeapplied` foi marcado.
- Se houve erro em `appliedmessage`.
- Se a tentativa foi recalculada.

### O feedback aparece em idioma inesperado

Verifique o idioma do usuÃ¡rio no Moodle. O prompt usa o idioma do usuÃ¡rio associado Ã  tentativa. ComentÃ¡rios jÃ¡ gravados anteriormente nÃ£o mudam retroativamente apÃ³s alteraÃ§Ã£o de idioma ou strings.

## LimitaÃ§Ãµes conhecidas

- Privacy provider implementado para metadados, exportacao e delecao dos dados de correcao; falta validacao real em Moodle instalado.
- Backup/restore ainda precisa de validaÃ§Ã£o real.
- Reprocessamento manual ainda nÃ£o estÃ¡ fechado como fluxo administrativo.
- Timeout e polÃ­tica de retry precisam de decisÃ£o final.
- Web services de enfileiramento/status existem como apoio/teste/admin e nÃ£o devem ser tratados como gatilho principal do Quiz.

## LicenÃ§as

O plugin segue GPL v3 ou posterior, conforme o padrÃ£o do Moodle.

Biblioteca de terceiros:

- CodeMirror 6, licenÃ§a MIT, empacotado localmente em `amd/src/codemirror6.js` e `amd/build/codemirror6.min.js`.

Consulte tambÃ©m:

```text
thirdpartylibs.xml
```

