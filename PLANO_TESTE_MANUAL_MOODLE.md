# Plano de Teste Manual no Moodle - qtype_codejudge

## Objetivo

Validar manualmente o plugin `qtype_codejudge` no Moodle, cobrindo:

- instalacao e atualizacao do plugin;
- criacao e edicao de questoes no banco de questoes;
- editor de codigo CodeMirror 6;
- linguagens suportadas;
- Portugol para algoritmos iniciantes;
- regra de conformidade de linguagem;
- Quiz com feedback adiado;
- Quiz com feedback imediato;
- Quiz em modo interativo com multiplas tentativas;
- correcao por IA via cron;
- comentario da autocorrecao;
- nota no question engine, resultado do Quiz e gradebook;
- revisao da tentativa;
- retomar tentativa, multiplas paginas e quizzes mistos.

## Versao Alvo

- Plugin: `qtype_codejudge`
- Versao minima esperada: `0.1.23`
- Pacote sugerido: `dist/qtype_codejudge_moodle_v26.zip`
- Moodle: 5.2 ou superior

## Ambiente de Teste

Registrar antes de iniciar:

- URL do Moodle:
- Versao do Moodle:
- Versao do plugin exibida no Moodle:
- Provedor de IA configurado:
- Modelo de IA:
- Idioma do usuario professor:
- Idioma do usuario aluno:
- Navegador:
- Sistema operacional:
- Data/hora do teste:

## Pre-condicoes

- Moodle instalado e acessivel.
- Usuario professor/admin com permissao para criar curso, Quiz e questoes.
- Usuario aluno disponivel para realizar tentativas.
- Cron do Moodle funcionando no mesmo codigo do plugin instalado.
- Provedor de IA configurado e com teste de conexao aprovado.
- Cache do Moodle limpo apos instalacao/atualizacao.
- Debug do Moodle habilitado durante testes tecnicos, se possivel.

Comandos uteis:

```bash
php admin/cli/upgrade.php
php admin/cli/purge_caches.php
php admin/cli/cron.php -f
```

Em Docker, adaptar para o container correto:

```bash
docker compose exec moodle php /var/www/moodle/admin/cli/upgrade.php
docker compose exec moodle php /var/www/moodle/admin/cli/purge_caches.php
docker compose exec moodle php /var/www/moodle/admin/cli/cron.php -f
```

## Massa de Teste

### Problema A - Contador de Vogais

Enunciado:

```text
Leia uma palavra e escreva a quantidade de vogais existentes nela.
Considere as vogais a, e, i, o, u em letras maiusculas ou minusculas.
```

Rubrica:

```text
Avalie se a solucao le a entrada, percorre todos os caracteres, identifica vogais maiusculas e minusculas, conta corretamente e imprime apenas o numero final.
```

Resposta correta em Python:

```python
palavra = input()
contador = 0
for letra in palavra.lower():
    if letra in "aeiou":
        contador += 1
print(contador)
```

Resposta correta em Portugol:

```text
leia palavra
contador <- 0

para cada letra em palavra faca
   letra <- minusculo(letra)
   se letra = "a" ou letra = "e" ou letra = "i" ou letra = "o" ou letra = "u" entao
      contador <- contador + 1
   fimse
fimpara

escreva contador
```

Resposta vaga que deve ser penalizada:

```text
Contar as vogais da palavra e mostrar o total.
```

### Problema B - Senha Valida

Enunciado:

```text
Leia uma senha numerica como texto. A senha e valida se a quantidade de digitos pares for maior que a quantidade de digitos impares. Escreva VALIDA ou INVALIDA.
```

Rubrica:

```text
Avalie se a solucao le a senha como texto, percorre todos os digitos, identifica corretamente pares e impares, compara as quantidades e imprime exatamente VALIDA ou INVALIDA.
```

Resposta correta em Python:

```python
senha = input()
pares = 0
impares = 0

for digito in senha:
    if int(digito) % 2 == 0:
        pares += 1
    else:
        impares += 1

if pares > impares:
    print("VALIDA")
else:
    print("INVALIDA")
```

Resposta com erro logico:

```python
senha = input()
pares = 0
impares = 0

for digito in senha:
    if int(digito) % 2 == 0:
        pares += 1
    else:
        impares += 1

if pares > impares:
    print("INVALIDA")
else:
    print("VALIDA")
```

## Criterios Gerais de Aprovacao

- O plugin instala ou atualiza sem erro.
- A questao aparece como `Coding question`.
- A questao pode ser criada, salva, reaberta, editada e duplicada.
- O enunciado aparece no preview e na tentativa.
- A rubrica nao aparece para o aluno.
- O editor carrega e mantem o valor submetido.
- O Quiz nao apresenta erro de sequencia ao verificar, salvar, navegar, retomar ou finalizar.
- A correcao por IA gera registro de fila, score, feedback e raw response.
- O comentario da autocorrecao aparece na revisao quando aplicavel.
- A nota da autocorrecao e aplicada ao question engine no fluxo correto.
- A nota consolidada aparece no resultado do Quiz e no gradebook.
- Respostas em linguagem diferente da configurada sao penalizadas.

## 1. Instalacao e Atualizacao

### 1.1 Instalar o plugin por ZIP

Passos:

1. Acessar `Administracao do site > Plugins > Instalar plugins`.
2. Enviar o ZIP `qtype_codejudge_moodle_v26.zip`.
3. Confirmar que o Moodle reconhece o plugin como `qtype_codejudge`.
4. Prosseguir com a instalacao.
5. Executar upgrade, se solicitado.
6. Limpar caches.

Resultado esperado:

- Instalacao concluida sem erro.
- Plugin aparece em `Administracao do site > Plugins > Tipos de questao`.
- Versao exibida e `0.1.23` ou superior.

Status:

- [ ] Aprovado
- [ ] Reprovado
- Observacoes:

### 1.2 Atualizar instalacao existente

Passos:

1. Substituir a pasta `question/type/codejudge` ou instalar ZIP por atualizacao.
2. Rodar upgrade do Moodle.
3. Limpar caches.
4. Confirmar versao `0.1.23`.
5. Verificar se web e cron usam o mesmo codigo do plugin.

Resultado esperado:

- Upgrade concluido sem erro.
- Cron nao exibe erro de classe invalida.
- Tabelas existentes preservam dados anteriores.

Status:

- [ ] Aprovado
- [ ] Reprovado
- Observacoes:

## 2. Configuracao da IA

### 2.1 Configurar provedor

Passos:

1. Acessar configuracoes do tipo de questao `Coding question`.
2. Selecionar provedor de IA.
3. Informar chave, base URL e modelo, quando aplicavel.
4. Clicar no teste de conexao.

Resultado esperado:

- Teste de conexao retorna sucesso.
- Falha de configuracao mostra mensagem clara.

Status:

- [ ] Aprovado
- [ ] Reprovado
- Observacoes:

### 2.2 Validar idioma do feedback

Passos:

1. Configurar usuario aluno em `pt_br`.
2. Realizar uma tentativa com resposta parcialmente errada.
3. Rodar cron.
4. Conferir idioma do feedback.
5. Repetir com usuario em ingles.

Resultado esperado:

- Feedback pedagogico vem no idioma do usuario do Moodle.
- Literais do problema, como `VALIDA` e `INVALIDA`, podem permanecer como especificados no enunciado.
- Labels do Moodle aparecem traduzidos conforme idioma.

Status:

- [ ] Aprovado
- [ ] Reprovado
- Observacoes:

## 3. Banco de Questoes

### 3.1 Criar questao Python

Passos:

1. Acessar banco de questoes.
2. Criar nova questao `Coding question`.
3. Usar Problema A.
4. Selecionar linguagem `Python`.
5. Preencher rubrica.
6. Salvar.
7. Reabrir a questao para edicao.

Resultado esperado:

- Questao salva sem erro.
- Linguagem, rubrica, codigo inicial e altura do editor persistem.
- Ao reabrir, os campos aparecem com os valores corretos.

Status:

- [ ] Aprovado
- [ ] Reprovado
- Observacoes:

### 3.2 Criar questao Portugol

Passos:

1. Criar nova questao `Coding question`.
2. Usar Problema A.
3. Selecionar linguagem `Portugol`.
4. Preencher rubrica.
5. Salvar.
6. Reabrir a questao.

Resultado esperado:

- `Portugol` aparece no seletor.
- Descricao de Portugol aparece para orientar algoritmo estruturado.
- Dados persistem ao reabrir.

Status:

- [ ] Aprovado
- [ ] Reprovado
- Observacoes:

### 3.3 Preview da questao

Passos:

1. Abrir preview da questao Python.
2. Abrir preview da questao Portugol.
3. Conferir enunciado, linguagem, editor e status.

Resultado esperado:

- Enunciado aparece antes do editor.
- Rubrica nao aparece para aluno.
- Linguagem permitida aparece.
- Editor carrega.
- Nao ha erro HTTP 500.

Status:

- [ ] Aprovado
- [ ] Reprovado
- Observacoes:

## 4. Editor de Codigo

### 4.1 Carregamento do CodeMirror 6

Passos:

1. Abrir questao no preview.
2. Confirmar numeracao de linhas.
3. Digitar codigo.
4. Usar Tab para indentar.
5. Usar undo/redo.
6. Conferir highlight da linguagem.

Resultado esperado:

- CodeMirror 6 carrega.
- Editor nao quebra layout.
- Teclado funciona.
- Valor digitado permanece no campo.

Status:

- [ ] Aprovado
- [ ] Reprovado
- Observacoes:

### 4.2 Fallback de textarea

Passos:

1. Bloquear temporariamente o carregamento do JS do CodeMirror, se possivel.
2. Abrir a questao.
3. Digitar resposta no textarea.
4. Submeter.

Resultado esperado:

- Textarea permanece utilizavel.
- Resposta e enviada ao Moodle.

Status:

- [ ] Aprovado
- [ ] Reprovado
- Observacoes:

### 4.3 Multiplas instancias na mesma pagina

Passos:

1. Criar Quiz com duas questoes `codejudge` na mesma pagina.
2. Abrir tentativa.
3. Digitar respostas diferentes em cada editor.
4. Salvar/finalizar.

Resultado esperado:

- Cada editor mantem seu proprio conteudo.
- Nao ha conflito de foco, linguagem ou valor enviado.

Status:

- [ ] Aprovado
- [ ] Reprovado
- Observacoes:

## 5. Quiz com Feedback Adiado

### 5.1 Criar Quiz com feedback adiado

Configuracao sugerida:

- Comportamento da questao: `Feedback adiado`.
- Duas questoes:
  - Q1 Python - Problema A.
  - Q2 Python - Problema B.
- Cada questao valendo 1 ponto.
- Mostrar nota e comentario na revisao.

Passos:

1. Criar Quiz.
2. Adicionar duas questoes `codejudge`.
3. Logar como aluno.
4. Iniciar tentativa.
5. Responder Q1 corretamente.
6. Responder Q2 com erro logico.
7. Finalizar tentativa.
8. Rodar cron ate processar tasks.
9. Abrir revisao.
10. Abrir relatorio de resultados.
11. Abrir gradebook.

Resultado esperado:

- Tentativa finaliza sem erro.
- Cron cria/processa uma task por questao respondida.
- Q1 recebe nota alta.
- Q2 recebe nota menor e feedback especifico.
- Comentario da autocorrecao aparece na revisao.
- Nota do Quiz e gradebook sao atualizados.

Status:

- [ ] Aprovado
- [ ] Reprovado
- Observacoes:

### 5.2 Finalizar tentativa antes do cron

Passos:

1. Finalizar tentativa.
2. Abrir revisao antes de rodar cron.
3. Rodar cron.
4. Atualizar revisao e relatorio.

Resultado esperado:

- Antes do cron, status indica aguardando/processando quando houver registro.
- Depois do cron, nota e comentario aparecem.
- Recalculo do Quiz ocorre sem acao manual extra.

Status:

- [ ] Aprovado
- [ ] Reprovado
- Observacoes:

### 5.3 Retomar tentativa

Passos:

1. Iniciar tentativa.
2. Responder Q1.
3. Sair sem finalizar.
4. Retomar tentativa.
5. Conferir resposta preservada.
6. Responder Q2.
7. Finalizar.
8. Rodar cron.

Resultado esperado:

- Resposta de Q1 e preservada.
- Nao ha duplicidade indevida de fila.
- Nota final e feedback aparecem apos cron.

Status:

- [ ] Aprovado
- [ ] Reprovado
- Observacoes:

### 5.4 Quiz misto

Passos:

1. Criar Quiz com:
   - uma questao `codejudge`;
   - uma questao multipla escolha;
   - uma questao dissertativa comum, se desejado.
2. Finalizar tentativa.
3. Rodar cron.
4. Conferir notas.

Resultado esperado:

- `codejudge` nao interfere nas outras questoes.
- Nota total soma corretamente.
- Gradebook exibe resultado consolidado.

Status:

- [ ] Aprovado
- [ ] Reprovado
- Observacoes:

## 6. Quiz com Feedback Imediato

### 6.1 Fluxo basico com uma questao

Configuracao sugerida:

- Comportamento da questao: `Feedback imediato`.
- Uma questao Python.
- Permitir revisao de feedback durante a tentativa, se a configuracao do Quiz permitir.

Passos:

1. Criar Quiz com feedback imediato.
2. Iniciar tentativa como aluno.
3. Responder a questao.
4. Clicar em `Verificar`.
5. Aguardar retorno da pagina.
6. Rodar cron.
7. Atualizar pagina ou retornar para questao.
8. Finalizar tentativa.
9. Rodar cron novamente.
10. Abrir revisao.

Resultado esperado:

- Clique em `Verificar` nao gera erro de sequencia.
- Tentativa continua aberta.
- Durante tentativa aberta, feedback/status da autocorrecao pode aparecer inline.
- Nota nao deve ser aplicada via `manual_grade()` enquanto tentativa estiver `inprogress`.
- Apos submissao final, nota e comentario entram no fluxo normal do question engine.

Status:

- [ ] Aprovado
- [ ] Reprovado
- Observacoes:

### 6.2 Duas questoes em paginas separadas

Passos:

1. Criar Quiz com feedback imediato.
2. Adicionar Q1 na pagina 1 e Q2 na pagina 2.
3. Iniciar tentativa.
4. Responder Q1.
5. Clicar em `Verificar`.
6. Avancar para Q2.
7. Responder Q2.
8. Clicar em `Verificar`.
9. Voltar para Q1.
10. Finalizar tentativa.
11. Rodar cron.

Resultado esperado:

- Nao aparece erro: `Voce enviou dados fora da sequencia normal`.
- Navegacao entre paginas funciona apos verificar.
- Cada questao mantem sua resposta.
- Tasks nao sobrescrevem respostas mais novas.
- Revisao final mostra comentarios corretos por questao.

Status:

- [ ] Aprovado
- [ ] Reprovado
- Observacoes:

### 6.3 Verificar e alterar resposta

Passos:

1. Responder com codigo incorreto.
2. Clicar em `Verificar`.
3. Alterar para codigo correto.
4. Clicar em `Verificar` novamente, se permitido.
5. Rodar cron entre as acoes, se necessario.
6. Finalizar tentativa.

Resultado esperado:

- Resultado antigo nao sobrescreve resposta mais nova.
- Registro de correcao fica associado ao step correto.
- Revisao final usa a resposta mais recente aplicavel.

Status:

- [ ] Aprovado
- [ ] Reprovado
- Observacoes:

## 7. Quiz Interativo com Multiplas Tentativas

### 7.1 Fluxo basico interativo

Configuracao sugerida:

- Comportamento da questao: `Interativo com multiplas tentativas`.
- Penalidades configuradas conforme padrao do Moodle.
- Uma questao Python.

Passos:

1. Criar Quiz no modo interativo.
2. Iniciar tentativa.
3. Enviar resposta errada.
4. Clicar em `Verificar`.
5. Observar feedback/status.
6. Corrigir resposta.
7. Clicar em `Tentar novamente` ou acao equivalente do Moodle, se exibida.
8. Enviar resposta correta.
9. Finalizar tentativa.
10. Rodar cron.
11. Revisar tentativa.

Resultado esperado:

- Plugin respeita fluxo interativo do Moodle.
- Nao ha erro de sequencia.
- Cada check gera ou reutiliza registro idempotente por step.
- Resultado atrasado de tentativa anterior nao sobrescreve resposta atual.
- Comentario formativo aparece quando disponivel.
- Nota final segue comportamento do Moodle e politicas de penalidade quando aplicavel.

Status:

- [ ] Aprovado
- [ ] Reprovado
- Observacoes:

### 7.2 Latencia da IA no modo interativo

Passos:

1. Enviar resposta.
2. Clicar em verificar.
3. Antes do cron terminar, navegar para outra pagina ou atualizar a questao.
4. Rodar cron.
5. Voltar para a questao.

Resultado esperado:

- A tentativa nao quebra.
- Status indica aguardando/processando/corrigido/erro.
- Feedback tardio aparece sem corromper a sequencia do Quiz.
- Nota so e aplicada em momento seguro.

Status:

- [ ] Aprovado
- [ ] Reprovado
- Observacoes:

## 8. Conformidade de Linguagem

### 8.1 Questao Python respondida em C

Passos:

1. Criar questao com linguagem `Python`.
2. Enviar resposta em C que resolva o problema corretamente.
3. Finalizar tentativa.
4. Rodar cron.
5. Conferir nota e feedback.

Resposta em C para teste:

```c
#include <stdio.h>
#include <string.h>

int main() {
    char palavra[1000];
    int contador = 0;
    scanf("%999s", palavra);
    for (int i = 0; palavra[i] != '\0'; i++) {
        char c = palavra[i];
        if (c >= 'A' && c <= 'Z') {
            c = c + 32;
        }
        if (c == 'a' || c == 'e' || c == 'i' || c == 'o' || c == 'u') {
            contador++;
        }
    }
    printf("%d\n", contador);
    return 0;
}
```

Resultado esperado:

- IA identifica linguagem incorreta.
- Nota nao deve ser alta.
- Se a logica estiver correta mas a linguagem errada, nota deve respeitar limite do prompt, no maximo 30.
- Feedback menciona que a resposta nao seguiu Python.

Status:

- [ ] Aprovado
- [ ] Reprovado
- Nota obtida:
- Observacoes:

### 8.2 Questao C respondida em Python

Passos:

1. Criar questao com linguagem `C`.
2. Enviar resposta correta em Python.
3. Finalizar tentativa.
4. Rodar cron.

Resultado esperado:

- Nota nao deve ser alta por nao seguir C.
- Feedback menciona linguagem incorreta.

Status:

- [ ] Aprovado
- [ ] Reprovado
- Nota obtida:
- Observacoes:

### 8.3 Questao Portugol respondida em Python

Passos:

1. Criar questao com linguagem `Portugol`.
2. Enviar resposta correta em Python.
3. Finalizar tentativa.
4. Rodar cron.

Resultado esperado:

- IA identifica que Python nao e resposta valida em Portugol.
- Nota deve ser limitada a valor baixo, idealmente no maximo 20.
- Feedback explica que deveria ser algoritmo estruturado em Portugol.

Status:

- [ ] Aprovado
- [ ] Reprovado
- Nota obtida:
- Observacoes:

### 8.4 Questao Portugol respondida com texto descritivo

Passos:

1. Criar questao com linguagem `Portugol`.
2. Enviar resposta: `Contar as vogais da palavra e imprimir a quantidade.`
3. Finalizar tentativa.
4. Rodar cron.

Resultado esperado:

- Nota baixa, idealmente no maximo 20.
- Feedback indica ausencia de comandos estruturados de entrada, processamento e saida.

Status:

- [ ] Aprovado
- [ ] Reprovado
- Nota obtida:
- Observacoes:

### 8.5 Questao Portugol respondida com algoritmo estruturado

Passos:

1. Criar questao com linguagem `Portugol`.
2. Enviar resposta correta em Portugol.
3. Finalizar tentativa.
4. Rodar cron.

Resultado esperado:

- Nota alta.
- Feedback reconhece entrada, processamento, saida e estrutura.

Status:

- [ ] Aprovado
- [ ] Reprovado
- Nota obtida:
- Observacoes:

## 9. Cron, Fila e Idempotencia

### 9.1 Uma correcao por questao/tentativa/resposta

Passos:

1. Finalizar tentativa com duas questoes `codejudge`.
2. Consultar tabela `qtype_codejudge_grading`.
3. Rodar cron.
4. Atualizar pagina de revisao.
5. Rodar cron novamente.
6. Consultar tabela novamente.

Resultado esperado:

- Uma correcao por questao/tentativa/resposta.
- Rodar cron novamente nao duplica correcao concluida.
- Status final `graded` ou `error`.

SQL util:

```sql
SELECT id, questionid, questionattemptid, questionattemptstepid, userid,
       language, status, score, gradeapplied, appliedmark,
       appliedstate, appliedmessage, timecreated, timemodified
  FROM mdl_qtype_codejudge_grading
 ORDER BY id DESC;
```

Status:

- [ ] Aprovado
- [ ] Reprovado
- Observacoes:

### 9.2 Erro do provedor de IA

Passos:

1. Configurar temporariamente provedor/chave invalida.
2. Finalizar tentativa.
3. Rodar cron.
4. Conferir status.
5. Restaurar configuracao correta.

Resultado esperado:

- Registro fica com status `error`.
- Mensagem de erro e armazenada.
- Quiz nao quebra.
- Professor/aluno ve estado coerente.

Status:

- [ ] Aprovado
- [ ] Reprovado
- Observacoes:

## 10. Revisao, Comentario e Gradebook

### 10.1 Revisao da tentativa

Passos:

1. Finalizar tentativa com feedback adiado.
2. Rodar cron.
3. Abrir revisao como aluno.
4. Abrir revisao como professor/admin.

Resultado esperado:

- Comentario da autocorrecao aparece.
- Label principal deve usar termos como `Comentario da autocorrecao` ou equivalente no idioma.
- Feedback completo aparece no comentario, nao duplicado no bloco de status.
- Nota da questao aparece corretamente.

Status:

- [ ] Aprovado
- [ ] Reprovado
- Observacoes:

### 10.2 Relatorio do Quiz

Passos:

1. Abrir relatorio de resultados do Quiz.
2. Conferir nota por questao.
3. Conferir nota total.

Resultado esperado:

- Questoes corrigidas nao ficam como `Requer avaliacao` apos cron e aplicacao da nota.
- Nota total reflete soma das questoes.

Status:

- [ ] Aprovado
- [ ] Reprovado
- Observacoes:

### 10.3 Gradebook

Passos:

1. Abrir livro de notas.
2. Conferir nota do Quiz.
3. Alterar/retentar Quiz, se permitido.
4. Rodar cron.
5. Conferir atualizacao.

Resultado esperado:

- Gradebook recebe nota final correta.
- Retentativas nao mantem nota antiga indevidamente.

Status:

- [ ] Aprovado
- [ ] Reprovado
- Observacoes:

## 11. Backup e Restore

### 11.1 Backup de curso com Quiz

Passos:

1. Criar curso com Quiz contendo questoes `codejudge`.
2. Fazer backup do curso.
3. Restaurar em novo curso.
4. Abrir banco de questoes restaurado.
5. Abrir Quiz restaurado.

Resultado esperado:

- Questoes `codejudge` sao restauradas.
- Linguagem, rubrica, codigo inicial e altura do editor persistem.
- Quiz restaurado abre tentativa.

Status:

- [ ] Aprovado
- [ ] Reprovado
- Observacoes:

### 11.2 Tentativa em Quiz restaurado

Passos:

1. Realizar tentativa no Quiz restaurado.
2. Finalizar.
3. Rodar cron.
4. Revisar nota e feedback.

Resultado esperado:

- Correcao funciona no curso restaurado.
- Gradebook do curso restaurado recebe nota.

Status:

- [ ] Aprovado
- [ ] Reprovado
- Observacoes:

## 12. Privacy e Dados Pessoais

Este item e de verificacao tecnica, pois o plugin armazena `userid` e codigo submetido.

Passos:

1. Verificar se o privacy provider do plugin declara dados pessoais armazenados.
2. Executar exportacao de dados de usuario, se o ambiente permitir.
3. Executar remocao de dados de usuario, se o ambiente permitir.

Resultado esperado:

- Dados pessoais armazenados sao declarados corretamente.
- Exportacao inclui registros relevantes ou documenta ausencia.
- Remocao/anomizacao segue politica definida.

Status:

- [ ] Aprovado
- [ ] Reprovado
- [ ] Bloqueado por implementacao pendente
- Observacoes:

## 13. Matriz de Navegadores

Executar pelo menos os cenarios principais nos navegadores abaixo:

| Navegador | Banco de questoes | Preview | Quiz feedback adiado | Quiz feedback imediato | Editor | Resultado |
| --- | --- | --- | --- | --- | --- | --- |
| Chrome/Chromium | [ ] | [ ] | [ ] | [ ] | [ ] | |
| Edge | [ ] | [ ] | [ ] | [ ] | [ ] | |
| Firefox | [ ] | [ ] | [ ] | [ ] | [ ] | |

Observacoes:

-

## 14. Matriz Final de Aprovacao

| Area | Status | Observacoes |
| --- | --- | --- |
| Instalacao/upgrade | [ ] Aprovado [ ] Reprovado | |
| Configuracao IA | [ ] Aprovado [ ] Reprovado | |
| Banco de questoes | [ ] Aprovado [ ] Reprovado | |
| Preview | [ ] Aprovado [ ] Reprovado | |
| Editor CodeMirror 6 | [ ] Aprovado [ ] Reprovado | |
| Feedback adiado | [ ] Aprovado [ ] Reprovado | |
| Feedback imediato | [ ] Aprovado [ ] Reprovado | |
| Interativo com multiplas tentativas | [ ] Aprovado [ ] Reprovado | |
| Conformidade de linguagem | [ ] Aprovado [ ] Reprovado | |
| Portugol | [ ] Aprovado [ ] Reprovado | |
| Cron/fila/idempotencia | [ ] Aprovado [ ] Reprovado | |
| Revisao/comentario | [ ] Aprovado [ ] Reprovado | |
| Gradebook | [ ] Aprovado [ ] Reprovado | |
| Backup/restore | [ ] Aprovado [ ] Reprovado | |
| Privacy | [ ] Aprovado [ ] Reprovado [ ] Pendente | |

## 15. Registro de Defeitos

Para cada defeito encontrado, registrar:

```text
ID:
Data:
Ambiente:
Usuario:
Quiz:
Questao:
Comportamento esperado:
Comportamento observado:
Passos para reproduzir:
Print/log:
Tabela qtype_codejudge_grading relacionada:
Severidade:
Status:
```

## 16. Decisao de Go/No-Go

Go para proxima fase somente se:

- feedback adiado estiver aprovado;
- feedback imediato nao apresentar erro de sequencia;
- nota e comentario forem aplicados corretamente apos finalizacao;
- gradebook receber nota correta;
- conformidade de linguagem estiver aprovada;
- Portugol estiver aprovado com resposta correta, resposta em Python e resposta vaga;
- cron processar fila sem duplicidade indevida;
- defeitos criticos estiverem corrigidos ou formalmente aceitos como pendencia.

Resultado final:

- [ ] Go
- [ ] No-Go

Responsavel:

Data:

Observacoes finais:



