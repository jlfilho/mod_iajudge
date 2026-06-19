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

$string['pluginname'] = 'Coding question';
$string['pluginname_help'] = 'A question type for code-based responses evaluated asynchronously by AI.';
$string['pluginname_link'] = 'question/type/codejudge';
$string['pluginnameadding'] = 'Adding a coding question';
$string['pluginnameediting'] = 'Editing a coding question';
$string['pluginnamesummary'] = 'A question type for programming answers with AI-based evaluation.';

$string['language'] = 'Programming language';
$string['allowedlanguage'] = 'Allowed language';
$string['rubric'] = 'Rubric';
$string['startercode'] = 'Starter code';
$string['editorheight'] = 'Editor height';
$string['responseeditor'] = 'Code editor';
$string['editor_help'] = 'Write your answer in the editor below. Press Tab to indent code.';
$string['lang_python'] = 'Python';
$string['lang_c'] = 'C';
$string['lang_java'] = 'Java';
$string['lang_javascript'] = 'JavaScript';
$string['lang_portugol'] = 'Portugol';
$string['langdesc_portugol'] = 'Use Portugol to write a structured algorithm, with commands such as leia, escreva, se, senao, enquanto, para, variables and assignments.';
$string['settings_heading'] = 'AI configuration';
$string['settings_heading_desc'] = 'Configure the provider used to test and evaluate codejudge submissions.';
$string['provider_core_ai'] = 'Moodle core AI';
$string['provider_openai'] = 'OpenAI';
$string['provider_anthropic'] = 'Anthropic';
$string['provider_gemini'] = 'Google Gemini';
$string['provider_ollama'] = 'Ollama';
$string['settings_provider'] = 'AI provider';
$string['settings_provider_desc'] = 'Select which provider the plugin should use.';
$string['settings_api_key'] = 'API key';
$string['settings_api_key_desc'] = 'Provider authentication token, when required.';
$string['settings_base_url'] = 'Base URL';
$string['settings_base_url_desc'] = 'Custom endpoint or proxy URL for providers that support it.';
$string['settings_model_name'] = 'Model name';
$string['settings_model_name_desc'] = 'Model identifier used by the selected provider.';
$string['test_connection'] = 'Test connection';
$string['test_connection_desc'] = 'Run a connectivity test against the configured provider.';
$string['test_connection_testing'] = 'Testing...';
$string['task_grade_submission'] = 'Codejudge assisted grading task';
$string['grading_status_queued'] = 'The grading request is queued.';
$string['grading_status_processing'] = 'The grading request is being processed.';
$string['grading_status_graded'] = 'The grading request has finished.';
$string['grading_status_error'] = 'The grading request failed.';
$string['ai_grading_status'] = 'Auto-grading status';
$string['ai_feedback_heading'] = 'Auto-grading comment';
$string['ai_score_line'] = 'Auto-grading result: {$a->score}% ({$a->mark}/{$a->maxmark}).';
$string['ai_score_percent'] = 'Auto-grading score: {$a}%';
$string['ai_feedback_saved'] = 'The feedback was recorded in the Moodle review comment.';
$string['ai_grade_applied'] = 'The auto-grading score was applied to the Moodle question grade.';
$string['ai_grade_not_applied'] = 'The auto-grading score has not been applied to the Moodle question grade yet.';
$string['connection_success'] = 'Connection succeeded.';
$string['connection_failed'] = 'Connection failed: {$a}';
$string['error_ai_response_invalid'] = 'The AI provider returned an invalid response.';
$string['error_provider_not_configured'] = 'The AI provider is not configured correctly.';
$string['error_invalid_question_type'] = 'The selected question is not a codejudge question.';
$string['error_invalid_userid'] = 'A valid user id is required to queue a grading request.';
$string['error_question_attempt_not_found'] = 'The question attempt was not found.';
$string['grading_status_unavailable'] = 'Grading status tracking is not available yet.';
$string['error_empty_rubric'] = 'The rubric cannot be empty.';
$string['error_no_language'] = 'Select a programming language.';
$string['error_invalid_language'] = 'Select a supported programming language.';
$string['error_invalid_editorheight'] = 'The editor height must be at least 200 pixels.';
$string['privacy:gradingrecords'] = 'AI grading records';
$string['privacy:metadata:qtype_codejudge_grading'] = 'Stores asynchronous AI grading requests and results for codejudge answers.';
$string['privacy:metadata:qtype_codejudge_grading:questionid'] = 'The question associated with the grading request.';
$string['privacy:metadata:qtype_codejudge_grading:questionattemptid'] = 'The Moodle question attempt associated with the grading request.';
$string['privacy:metadata:qtype_codejudge_grading:questionattemptstepid'] = 'The question attempt step associated with the submitted answer.';
$string['privacy:metadata:qtype_codejudge_grading:userid'] = 'The user who submitted the answer.';
$string['privacy:metadata:qtype_codejudge_grading:language'] = 'The selected or submitted programming language.';
$string['privacy:metadata:qtype_codejudge_grading:code'] = 'The submitted source code.';
$string['privacy:metadata:qtype_codejudge_grading:rubric'] = 'The grading rubric used to build the AI prompt.';
$string['privacy:metadata:qtype_codejudge_grading:prompt'] = 'The prompt sent to the configured AI provider.';
$string['privacy:metadata:qtype_codejudge_grading:status'] = 'The current status of the grading request.';
$string['privacy:metadata:qtype_codejudge_grading:score'] = 'The score returned by the AI provider.';
$string['privacy:metadata:qtype_codejudge_grading:feedback'] = 'The feedback returned by the AI provider.';
$string['privacy:metadata:qtype_codejudge_grading:rawresponse'] = 'The raw response returned by the AI provider.';
$string['privacy:metadata:qtype_codejudge_grading:errormessage'] = 'Any error message captured while processing the grading request.';
$string['privacy:metadata:qtype_codejudge_grading:gradeapplied'] = 'Whether the AI result was applied to the Moodle question grade.';
$string['privacy:metadata:qtype_codejudge_grading:appliedmark'] = 'The mark applied to the Moodle question attempt.';
$string['privacy:metadata:qtype_codejudge_grading:appliedstate'] = 'The question attempt state after applying the mark.';
$string['privacy:metadata:qtype_codejudge_grading:appliedmessage'] = 'A diagnostic message about applying the mark.';
$string['privacy:metadata:qtype_codejudge_grading:timegradeapplied'] = 'The time when the mark was applied.';
$string['privacy:metadata:qtype_codejudge_grading:timecreated'] = 'The time when the grading request was created.';
$string['privacy:metadata:qtype_codejudge_grading:timemodified'] = 'The time when the grading request was last modified.';
$string['privacy:metadata:ai_provider'] = 'Submitted code, rubric, prompt text and generated feedback may be sent to the configured AI provider for automatic grading.';
$string['privacy:metadata:ai_provider:prompt'] = 'The complete prompt sent to the AI provider.';
$string['privacy:metadata:ai_provider:code'] = 'The submitted code included in the prompt.';
$string['privacy:metadata:ai_provider:rubric'] = 'The grading rubric included in the prompt.';
$string['privacy:metadata:ai_provider:feedback'] = 'The feedback generated by the AI provider.';
