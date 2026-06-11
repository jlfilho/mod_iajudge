/**
 * Submission status polling for mod_iajudge.
 *
 * @package     mod_iajudge
 * @copyright   2026 IA Judge Contributors
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define(['jquery', 'core/ajax', 'core/templates', 'core/notification'], function($, Ajax, Templates, Notification) {
    return {
        /**
         * Initialize polling for pending submissions.
         *
         * @param {Object} config Configuration containing:
         *                        - submissionids: Array of submission IDs to poll.
         *                        - pollinterval: How often to poll in ms (default 5000).
         */
        init: function(config) {
            var pendingIds = config.submissionids || [];
            var interval = config.pollinterval || 5000;

            if (pendingIds.length === 0) {
                return;
            }

            var pollTimer = setInterval(function() {
                var requests = [];
                pendingIds.forEach(function(id) {
                    requests.push({
                        methodname: 'mod_iajudge_get_submission_status',
                        args: { submissionid: id }
                    });
                });

                var promises = Ajax.call(requests);

                promises.forEach(function(promise, index) {
                    var submissionId = pendingIds[index];

                    promise.then(function(data) {
                        // Check if the submission status has updated from pending/processing.
                        if (data.status === 'graded' || data.status === 'error') {
                            // Stop polling this specific submission.
                            pendingIds = pendingIds.filter(function(id) {
                                return id !== submissionId;
                            });

                            // Update the row or box for this submission in the DOM.
                            var container = $('#ia-judge-submission-' + submissionId);
                            if (container.length) {
                                // Render the updated result using Moodle templates.
                                Templates.render('mod_iajudge/submission_result', data)
                                    .then(function(html, js) {
                                        Templates.replaceNodeContents(container, html, js);
                                    })
                                    .catch(Notification.exception);
                            }

                            // If no more submissions are pending, stop the timer completely.
                            if (pendingIds.length === 0) {
                                clearInterval(pollTimer);
                                // Reload page if it's the first submission to make sure overall UI is fresh.
                                // Or let the user enjoy the AJAX update.
                            }
                        } else {
                            // Update the badge text / class to reflect "processing" vs "pending".
                            var statusBadge = $('#ia-judge-status-badge-' + submissionId);
                            if (statusBadge.length) {
                                if (data.status === 'processing') {
                                    statusBadge.text(M.util.get_string('status_processing', 'mod_iajudge'));
                                    statusBadge.removeClass('badge-secondary').addClass('badge-info');
                                }
                            }
                        }
                    }).catch(function(error) {
                        // On a communication error, we don't crash, just log and keep polling.
                        console.error('Failed to fetch submission status for ID: ' + submissionId, error);
                    });
                });

                // Safety check to clear timer if array gets emptied elsewhere.
                if (pendingIds.length === 0) {
                    clearInterval(pollTimer);
                }
            }, interval);
        }
    };
});
