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

/**
 * Portuguese Brazilian language strings for mod_iajudge.
 *
 * @package     mod_iajudge
 * @copyright   2026 IA Judge Contributors
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

// ---------------------------------------------------------------------------
// Identidade do Plugin
// ---------------------------------------------------------------------------
$string['pluginname']      = 'CodeReview IA';
$string['modulename']      = 'CodeReview IA';
$string['modulenameplural'] = 'CodeReview IA';
$string['pluginadministration'] = 'Administração do CodeReview IA';
$string['modulename_help'] = 'A atividade CodeReview IA permite que os alunos enviem código-fonte (Python, C, Java, JavaScript) que é avaliado de forma assíncrona por um modelo de IA. A IA retorna uma nota numérica e um feedback pedagógico com base em uma rubrica definida pelo professor.';

// ---------------------------------------------------------------------------
// mod_form.php — Formulário do Professor
// ---------------------------------------------------------------------------
$string['rubric_prompt']      = 'Rubrica de Correção (Instruções para a IA)';
$string['rubric_prompt_help'] = 'Insira as instruções e critérios de avaliação que serão enviados para a IA. Seja específico sobre a distribuição dos pontos. Exemplo: "Avalie a lógica (40%), boas práticas e nomes de variáveis (30%) e complexidade do algoritmo (30%). Não forneça a resposta — aponte onde melhorar."';
$string['rubric_prompt_placeholder'] = 'Avalie o código com base nos seguintes critérios:
- Correção lógica (40%): O algoritmo resolve o problema corretamente?
- Qualidade do código (30%): Nomes de variáveis significativos, comentários e organização do código.
- Eficiência (30%): Considerações de complexidade de tempo e espaço.

Não revele a solução completa. Aponte exatamente o que precisa ser corrigido.';

$string['allowed_languages']      = 'Linguagens de Programação Permitidas';
$string['allowed_languages_help'] = 'Selecione quais linguagens de programação os alunos podem usar para seus envios nesta atividade.';
$string['lang_python']            = 'Python';
$string['lang_c']                 = 'C';
$string['lang_java']              = 'Java';
$string['lang_javascript']        = 'JavaScript';
$string['question_bank']          = 'Questões de Codificação';
$string['question_default_mark']   = 'Nota padrão: {$a}';
$string['no_codejudge_questions']  = 'Nenhuma questão de codificação foi encontrada no banco de questões deste curso.';
$string['error_no_questions_selected'] = 'Selecione ao menos uma questão de codificação do banco de questões.';

$string['max_attempts']      = 'Tentativas Máximas';
$string['max_attempts_help'] = 'Número máximo de submissões de código por aluno. Defina como 0 para tentativas ilimitadas.';
$string['unlimited']         = 'Ilimitado';

// ---------------------------------------------------------------------------
// view.php — Interface do Aluno
// ---------------------------------------------------------------------------
$string['submit_code']           = 'Enviar para Avaliação';
$string['submitting']            = 'Enviando…';
$string['select_language']       = 'Linguagem de Programação';
$string['select_language_prompt'] = '— Selecione uma linguagem —';
$string['code_editor_label']     = 'Seu Código';
$string['editor_theme_dark']     = 'Tema Escuro';
$string['editor_theme_light']    = 'Tema Claro';

$string['attempts_remaining']    = 'Tentativas restantes: {$a}';
$string['no_attempts_remaining'] = 'Você utilizou todas as suas tentativas de envio para esta atividade.';
$string['submission_received']   = 'Seu código foi enviado e está na fila para avaliação por IA.';

// ---------------------------------------------------------------------------
// Status da Submissão
// ---------------------------------------------------------------------------
$string['status_pending']    = 'Pendente';
$string['status_processing'] = 'Processando';
$string['status_graded']     = 'Avaliado';
$string['status_error']      = 'Erro';

$string['your_score']     = 'Sua Nota';
$string['ai_feedback']    = 'Feedback da IA';
$string['submitted_at']   = 'Enviado em';
$string['language_label'] = 'Linguagem';
$string['no_submissions'] = 'Nenhum envio feito.';

// ---------------------------------------------------------------------------
// Painel do Professor
// ---------------------------------------------------------------------------
$string['all_submissions']   = 'Todos os Envios';
$string['student']           = 'Estudante';
$string['submission_time']   = 'Hora do Envio';
$string['evaluation_status'] = 'Status';
$string['score']             = 'Nota';
$string['view_details']      = 'Ver Detalhes';

// ---------------------------------------------------------------------------
// UI / Strings Adicionais das Templates
// ---------------------------------------------------------------------------
$string['code_editor']           = 'Editor de Código';
$string['your_attempts']          = 'Seus Envios';
$string['no_attempts_yet']        = 'Nenhuma tentativa feita ainda';
$string['submit_first_attempt']   = 'Envie seu primeiro código para receber feedback da IA.';
$string['attempt']                = 'Envio';
$string['submitted_code']         = 'Código Enviado';
$string['processing_desc']        = 'Por favor, aguarde enquanto o modelo de IA avalia seu código.';
$string['error_desc']             = 'Ocorreu um erro durante a avaliação. Por favor, tente novamente.';
$string['teacher_dashboard']      = 'Painel do Professor';
$string['date']                   = 'Data';
$string['language']               = 'Linguagem';
$string['status']                 = 'Status';
$string['actions']                = 'Ações';
$string['no_submissions_yet']     = 'Nenhum envio de estudante feito ainda.';
$string['status_sending']         = 'Enviando…';

// ---------------------------------------------------------------------------
// settings.php — Configuração Global do Admin
// ---------------------------------------------------------------------------
$string['settings_heading']         = 'Configuração do Provedor de IA';
$string['settings_heading_desc']    = 'Configure o provedor de IA que será utilizado para avaliar as submissões de código dos alunos.';

$string['settings_provider']        = 'Provedor de IA';
$string['settings_provider_desc']   = 'Selecione o provedor de serviço de IA a ser usado para avaliação de código. A API de IA do core do Moodle fica disponível quando o site estiver configurado para isso.';
$string['provider_openai']          = 'OpenAI (GPT-4o, etc.)';
$string['provider_core_ai']         = 'API de IA do core do Moodle';
$string['provider_anthropic']       = 'Anthropic Claude (Claude 3.5 Sonnet, etc.)';
$string['provider_gemini']          = 'Google Gemini (Gemini 1.5 Pro/Flash, etc.)';
$string['provider_ollama']          = 'Ollama (Local: Llama 3, Mistral, etc.)';

$string['settings_api_key']         = 'Chave da API (API Key)';
$string['settings_api_key_desc']    = 'Chave de API secreta para o provedor selecionado. Não é necessária para o Ollama.';

$string['settings_base_url']        = 'URL Base / Endpoint';
$string['settings_base_url_desc']   = 'URL base personalizada para o endpoint da API. Necessária para o Ollama (ex: http://localhost:11434) ou para proxies corporativos.';

$string['settings_model_name']      = 'Nome do Modelo';
$string['settings_model_name_desc'] = 'O modelo específico a ser usado (ex: gpt-4o, claude-3-5-sonnet-20241022, gemini-1.5-pro, llama3).';

$string['test_connection']          = 'Testar Conexão';
$string['test_connection_desc']     = 'Clique no botão abaixo após salvar suas configurações para verificar se o provedor de IA está acessível e se as credenciais são válidas.';
$string['test_connection_testing']   = 'Testando conexão...';
$string['connection_success']       = 'Conexão bem-sucedida! O provedor de IA respondeu corretamente.';
$string['connection_failed']        = 'Falha na conexão: {$a}';

// ---------------------------------------------------------------------------
// Mensagens de Erro
// ---------------------------------------------------------------------------
$string['error_no_language']        = 'Por favor, selecione uma linguagem de programação.';
$string['error_empty_code']         = 'O editor de código está vazio. Por favor, escreva algum código antes de enviar.';
$string['error_empty_rubric']       = 'A rubrica não pode ficar vazia. Forneça as instruções de avaliação antes de salvar.';
$string['error_provider_not_configured'] = 'O provedor de IA não está configurado. Por favor, peça ao administrador do site para definir as configurações do Avaliador de IA.';
$string['error_ai_response_invalid'] = 'A IA retornou um formato de resposta inesperado. Por favor, tente novamente ou entre em contato com o suporte.';
$string['error_submission_not_found'] = 'Envio não encontrado.';
$string['error_access_denied']      = 'Você não tem permissão para visualizar este envio.';

// ---------------------------------------------------------------------------
// Privacidade / GDPR
// ---------------------------------------------------------------------------
$string['privacy:metadata:iajudge_submission']              = 'Informações sobre os envios de código dos estudantes.';
$string['privacy:metadata:iajudge_submission:userid']       = 'O ID do usuário que enviou o código.';
$string['privacy:metadata:iajudge_submission:language']     = 'A linguagem de programação selecionada pelo estudante.';
$string['privacy:metadata:iajudge_submission:code']         = 'O código-fonte enviado pelo estudante.';
$string['privacy:metadata:iajudge_submission:timecreated']  = 'O momento em que o envio foi realizado.';
$string['privacy:metadata:iajudge_grade']                   = 'Resultados da avaliação da IA para os envios dos estudantes.';
$string['privacy:metadata:iajudge_grade:score']             = 'A nota numérica atribuída pela IA.';
$string['privacy:metadata:iajudge_grade:feedback']          = 'O feedback pedagógico fornecido pela IA.';
$string['privacy:metadata:external_ai_provider']             = 'O código do estudante pode ser enviado para um provedor de IA externo (OpenAI, Anthropic, Google Gemini ou uma instância local do Ollama) ou processado por APIs de IA do core do Moodle para avaliação.';
