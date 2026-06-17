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
 * English language strings for mod_iajudge.
 *
 * @package     mod_iajudge
 * @copyright   2026 IA Judge Contributors
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

// ---------------------------------------------------------------------------
// Plugin identity
// ---------------------------------------------------------------------------
$string['pluginname']      = 'IA Judge';
$string['modulename']      = 'IA Code Judge';
$string['modulenameplural'] = 'IA Code Judges';
$string['pluginadministration'] = 'IA Judge administration';
$string['modulename_help'] = 'The IA Judge activity allows students to submit source code (Python, C, Java, JavaScript) which is then evaluated asynchronously by an AI model. The AI returns a numeric score and pedagogical feedback based on a rubric defined by the teacher.';

// ---------------------------------------------------------------------------
// mod_form.php — Teacher configuration form
// ---------------------------------------------------------------------------
$string['rubric_prompt']      = 'Correction Rubric (AI Instructions)';
$string['rubric_prompt_help'] = 'Enter the instructions and evaluation criteria that will be sent to the AI. Be specific about the scoring breakdown. Example: "Evaluate logic (40%), good practices and variable names (30%), and algorithm complexity (30%). Do not give away the answer — point out where to improve."';
$string['rubric_prompt_placeholder'] = 'Evaluate the code based on the following criteria:
- Logic correctness (40%): Does the algorithm solve the problem correctly?
- Code quality (30%): Meaningful variable names, comments, and code organisation.
- Efficiency (30%): Time and space complexity considerations.

Do NOT reveal the complete solution. Point out exactly what needs to be fixed.';

$string['allowed_languages']      = 'Allowed Programming Languages';
$string['allowed_languages_help'] = 'Select which programming languages students may use for their submissions in this activity.';
$string['lang_python']            = 'Python';
$string['lang_c']                 = 'C';
$string['lang_java']              = 'Java';
$string['lang_javascript']        = 'JavaScript';
$string['question_bank']          = 'Coding Questions';
$string['question_default_mark']   = 'Default mark: {$a}';
$string['no_codejudge_questions']  = 'No coding questions were found in the question bank for this course.';
$string['error_no_questions_selected'] = 'Select at least one coding question from the question bank.';

$string['max_attempts']      = 'Maximum Attempts';
$string['max_attempts_help'] = 'Maximum number of code submissions per student. Set to 0 for unlimited attempts.';
$string['unlimited']         = 'Unlimited';

// ---------------------------------------------------------------------------
// view.php — Student submission interface
// ---------------------------------------------------------------------------
$string['submit_code']           = 'Submit for Evaluation';
$string['submitting']            = 'Submitting…';
$string['select_language']       = 'Programming Language';
$string['select_language_prompt'] = '— Select a language —';
$string['code_editor_label']     = 'Your Code';
$string['editor_theme_dark']     = 'Dark Theme';
$string['editor_theme_light']    = 'Light Theme';

$string['attempts_remaining']    = 'Attempts remaining: {$a}';
$string['no_attempts_remaining'] = 'You have used all your submission attempts for this activity.';
$string['submission_received']   = 'Your code has been submitted and is queued for AI evaluation.';

// ---------------------------------------------------------------------------
// Submission status
// ---------------------------------------------------------------------------
$string['status_pending']    = 'Pending';
$string['status_processing'] = 'Processing';
$string['status_graded']     = 'Graded';
$string['status_error']      = 'Error';

$string['your_score']     = 'Your Score';
$string['ai_feedback']    = 'AI Feedback';
$string['submitted_at']   = 'Submitted at';
$string['language_label'] = 'Language';
$string['no_submissions'] = 'No submissions yet.';

// ---------------------------------------------------------------------------
// Teacher / grading view
// ---------------------------------------------------------------------------
$string['all_submissions']   = 'All Submissions';
$string['student']           = 'Student';
$string['submission_time']   = 'Submission Time';
$string['evaluation_status'] = 'Status';
$string['score']             = 'Score';
$string['view_details']      = 'View Details';

// ---------------------------------------------------------------------------
// settings.php — Admin global configuration
// ---------------------------------------------------------------------------
$string['settings_heading']         = 'AI Provider Configuration';
$string['settings_heading_desc']    = 'Configure the AI provider that will be used to evaluate student code submissions.';

$string['settings_provider']        = 'AI Provider';
$string['settings_provider_desc']   = 'Select the AI service provider to use for code evaluation. Moodle core AI API is available when the site is configured for it.';
$string['provider_openai']          = 'OpenAI (GPT-4o, etc.)';
$string['provider_core_ai']         = 'Moodle core AI API';
$string['provider_anthropic']       = 'Anthropic Claude (Claude 3.5 Sonnet, etc.)';
$string['provider_gemini']          = 'Google Gemini (Gemini 1.5 Pro/Flash, etc.)';
$string['provider_ollama']          = 'Ollama (Local: Llama 3, Mistral, etc.)';

$string['settings_api_key']         = 'API Key';
$string['settings_api_key_desc']    = 'Secret API key for the selected provider. Not required for Ollama.';

$string['settings_base_url']        = 'Base URL / Endpoint';
$string['settings_base_url_desc']   = 'Custom base URL for the API endpoint. Required for Ollama (e.g. http://localhost:11434) and for enterprise proxies.';

$string['settings_model_name']      = 'Model Name';
$string['settings_model_name_desc'] = 'The specific model to use (e.g. gpt-4o, claude-3-5-sonnet-20241022, gemini-1.5-pro, llama3).';

$string['test_connection']          = 'Test Connection';
$string['test_connection_desc']     = 'Click the button below after saving your settings to verify the AI provider is reachable and the credentials are valid.';
$string['test_connection_testing']   = 'Testing connection...';
$string['connection_success']       = 'Connection successful! The AI provider responded correctly.';
$string['connection_failed']        = 'Connection failed: {$a}';

// ---------------------------------------------------------------------------
// ---------------------------------------------------------------------------
// UI / Additional strings
// ---------------------------------------------------------------------------
$string['code_editor']           = 'Code Editor';
$string['your_attempts']          = 'Your Submissions';
$string['no_attempts_yet']        = 'No attempts yet';
$string['submit_first_attempt']   = 'Submit your first code to receive AI feedback.';
$string['attempt']                = 'Submission';
$string['submitted_code']         = 'Submitted Code';
$string['processing_desc']        = 'Please wait while the AI model evaluates your code.';
$string['error_desc']             = 'An error occurred during evaluation. Please try again.';
$string['teacher_dashboard']      = 'Teacher Dashboard';
$string['date']                   = 'Date';
$string['language']               = 'Language';
$string['status']                 = 'Status';
$string['actions']                = 'Actions';
$string['no_submissions_yet']     = 'No student submissions yet.';
$string['status_sending']         = 'Sending…';

// ---------------------------------------------------------------------------
// Error messages
// ---------------------------------------------------------------------------
$string['error_no_language']        = 'Please select a programming language.';
$string['error_empty_code']         = 'The code editor is empty. Please write some code before submitting.';
$string['error_empty_rubric']       = 'The rubric cannot be empty. Please provide evaluation instructions before saving.';
$string['error_provider_not_configured'] = 'The AI provider is not configured. Please ask your site administrator to set up the IA Judge settings.';
$string['error_ai_response_invalid'] = 'The AI returned an unexpected response format. Please try again or contact support.';
$string['error_submission_not_found'] = 'Submission not found.';
$string['error_access_denied']      = 'You do not have permission to view this submission.';

// ---------------------------------------------------------------------------
// Privacy / GDPR
// ---------------------------------------------------------------------------
$string['privacy:metadata:iajudge_submission']              = 'Information about student code submissions.';
$string['privacy:metadata:iajudge_submission:userid']       = 'The ID of the user who submitted the code.';
$string['privacy:metadata:iajudge_submission:language']     = 'The programming language selected by the student.';
$string['privacy:metadata:iajudge_submission:code']         = 'The source code submitted by the student.';
$string['privacy:metadata:iajudge_submission:timecreated']  = 'The time when the submission was made.';
$string['privacy:metadata:iajudge_grade']                   = 'AI evaluation results for student submissions.';
$string['privacy:metadata:iajudge_grade:score']             = 'The numeric score assigned by the AI.';
$string['privacy:metadata:iajudge_grade:feedback']          = 'Pedagogical feedback provided by the AI.';
$string['privacy:metadata:external_ai_provider']             = 'Student code may be sent to an external AI provider (OpenAI, Anthropic, Google Gemini, or a local Ollama instance) or processed through Moodle core AI APIs for evaluation.';
