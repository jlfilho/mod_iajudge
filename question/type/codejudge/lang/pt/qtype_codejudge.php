<?php
// This file is part of Moodle - https://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

defined('MOODLE_INTERNAL') || die();

$string['ai_grading_status'] = 'Status da autocorreção';
$string['ai_feedback_heading'] = 'Comentário da autocorreção';
$string['ai_score_line'] = 'Resultado da autocorreção: {$a->score}% ({$a->mark}/{$a->maxmark}).';
$string['ai_score_percent'] = 'Nota da autocorreção: {$a}%';
$string['ai_feedback_saved'] = 'O feedback foi registrado no comentario de revisao do Moodle.';
$string['ai_grade_applied'] = 'A nota da autocorreção foi aplicada na nota da questao no Moodle.';
$string['ai_grade_not_applied'] = 'A nota da autocorreção ainda nao foi aplicada na nota da questao no Moodle.';
$string['error_question_attempt_not_found'] = 'A tentativa da questao nao foi encontrada.';

$string['pluginname'] = 'Questão de código';
$string['pluginname_help'] = 'Um tipo de questão para respostas em código avaliadas assincronamente por IA.';
$string['pluginname_link'] = 'question/type/codejudge';
$string['pluginnameadding'] = 'Adicionando uma questão de código';
$string['pluginnameediting'] = 'Editando uma questão de código';
$string['pluginnamesummary'] = 'Tipo de questão para respostas em programação com avaliação por IA.';

$string['language'] = 'Linguagem de programação';
$string['allowedlanguage'] = 'Linguagem permitida';
$string['rubric'] = 'Rubrica de correção';
$string['startercode'] = 'Código inicial';
$string['editorheight'] = 'Altura do editor';
$string['responseeditor'] = 'Editor de código';
$string['editor_help'] = 'Escreva sua resposta no editor abaixo. Pressione Tab para indentar o código.';
$string['lang_python'] = 'Python';
$string['lang_c'] = 'C';
$string['lang_java'] = 'Java';
$string['lang_javascript'] = 'JavaScript';
$string['lang_portugol'] = 'Portugol';
$string['langdesc_portugol'] = 'Use Portugol para escrever um algoritmo estruturado, com comandos como leia, escreva, se, senão, enquanto, para, variáveis e atribuições.';
$string['settings_heading'] = 'Configuração de IA';
$string['settings_heading_desc'] = 'Configure o provedor usado para testar e avaliar submissões do codejudge.';
$string['provider_core_ai'] = 'IA central do Moodle';
$string['provider_openai'] = 'OpenAI';
$string['provider_anthropic'] = 'Anthropic';
$string['provider_gemini'] = 'Google Gemini';
$string['provider_ollama'] = 'Ollama';
$string['settings_provider'] = 'Provedor de IA';
$string['settings_provider_desc'] = 'Selecione qual provedor o plugin deve usar.';
$string['settings_api_key'] = 'Chave de API';
$string['settings_api_key_desc'] = 'Token de autenticação do provedor, quando necessário.';
$string['settings_base_url'] = 'URL base';
$string['settings_base_url_desc'] = 'Endpoint personalizado ou URL de proxy para provedores que suportam isso.';
$string['settings_model_name'] = 'Nome do modelo';
$string['settings_model_name_desc'] = 'Identificador do modelo usado pelo provedor selecionado.';
$string['test_connection'] = 'Testar conexão';
$string['test_connection_desc'] = 'Executa um teste de conectividade com o provedor configurado.';
$string['test_connection_testing'] = 'Testando...';
$string['task_grade_submission'] = 'Tarefa de correção por IA do codejudge';
$string['grading_status_queued'] = 'A solicitação de correção está na fila.';
$string['grading_status_processing'] = 'A solicitação de correção está em processamento.';
$string['grading_status_graded'] = 'A solicitação de correção foi concluída.';
$string['grading_status_error'] = 'A solicitação de correção falhou.';
$string['connection_success'] = 'Conexão realizada com sucesso.';
$string['connection_failed'] = 'Falha na conexão: {$a}';
$string['error_ai_response_invalid'] = 'O provedor de IA retornou uma resposta inválida.';
$string['error_provider_not_configured'] = 'O provedor de IA não está configurado corretamente.';
$string['error_invalid_question_type'] = 'A questão selecionada não é uma questão codejudge.';
$string['error_invalid_userid'] = 'Um ID de usuário válido é necessário para enfileirar uma solicitação de correção.';
$string['grading_status_unavailable'] = 'O rastreamento do status de correção ainda não está disponível.';
$string['error_empty_rubric'] = 'A rubrica não pode ficar vazia.';
$string['error_no_language'] = 'Selecione uma linguagem de programação.';
$string['error_invalid_language'] = 'Selecione uma linguagem de programação suportada.';
$string['error_invalid_editorheight'] = 'A altura do editor deve ser de pelo menos 200 pixels.';
$string['privacy:gradingrecords'] = 'Registros de correção por IA';
$string['privacy:metadata:qtype_codejudge_grading'] = 'Armazena solicitações e resultados de correção por IA para respostas codejudge.';
$string['privacy:metadata:qtype_codejudge_grading:questionid'] = 'A questão associada à solicitação de correção.';
$string['privacy:metadata:qtype_codejudge_grading:questionattemptid'] = 'A tentativa de questão do Moodle associada à solicitação de correção.';
$string['privacy:metadata:qtype_codejudge_grading:questionattemptstepid'] = 'O step da tentativa de questão associado à resposta enviada.';
$string['privacy:metadata:qtype_codejudge_grading:userid'] = 'O usuário que enviou a resposta.';
$string['privacy:metadata:qtype_codejudge_grading:language'] = 'A linguagem de programação selecionada ou enviada.';
$string['privacy:metadata:qtype_codejudge_grading:code'] = 'O código-fonte enviado.';
$string['privacy:metadata:qtype_codejudge_grading:rubric'] = 'A rubrica usada para montar o prompt da IA.';
$string['privacy:metadata:qtype_codejudge_grading:prompt'] = 'O prompt enviado ao provedor de IA configurado.';
$string['privacy:metadata:qtype_codejudge_grading:status'] = 'O status atual da solicitação de correção.';
$string['privacy:metadata:qtype_codejudge_grading:score'] = 'A pontuação retornada pelo provedor de IA.';
$string['privacy:metadata:qtype_codejudge_grading:feedback'] = 'O feedback retornado pelo provedor de IA.';
$string['privacy:metadata:qtype_codejudge_grading:rawresponse'] = 'A resposta bruta retornada pelo provedor de IA.';
$string['privacy:metadata:qtype_codejudge_grading:errormessage'] = 'Mensagem de erro capturada durante o processamento da correção.';
$string['privacy:metadata:qtype_codejudge_grading:gradeapplied'] = 'Se o resultado da IA foi aplicado na nota da questão no Moodle.';
$string['privacy:metadata:qtype_codejudge_grading:appliedmark'] = 'A nota aplicada na tentativa da questão no Moodle.';
$string['privacy:metadata:qtype_codejudge_grading:appliedstate'] = 'O estado da tentativa da questão após aplicar a nota.';
$string['privacy:metadata:qtype_codejudge_grading:appliedmessage'] = 'Mensagem diagnóstica sobre a aplicação da nota.';
$string['privacy:metadata:qtype_codejudge_grading:timegradeapplied'] = 'O momento em que a nota foi aplicada.';
$string['privacy:metadata:qtype_codejudge_grading:timecreated'] = 'O momento em que a solicitação de correção foi criada.';
$string['privacy:metadata:qtype_codejudge_grading:timemodified'] = 'O momento em que a solicitação de correção foi modificada pela última vez.';
$string['privacy:metadata:ai_provider'] = 'Código enviado, rubrica, prompt e feedback gerado podem ser enviados ao provedor de IA configurado para correção automática.';
$string['privacy:metadata:ai_provider:prompt'] = 'O prompt completo enviado ao provedor de IA.';
$string['privacy:metadata:ai_provider:code'] = 'O código enviado incluído no prompt.';
$string['privacy:metadata:ai_provider:rubric'] = 'A rubrica de correção incluída no prompt.';
$string['privacy:metadata:ai_provider:feedback'] = 'O feedback gerado pelo provedor de IA.';
