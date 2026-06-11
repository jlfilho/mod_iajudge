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
 * Lists all iajudge instances in a given course.
 *
 * Required by Moodle for every mod_* plugin.
 *
 * @package     mod_iajudge
 * @copyright   2026 IA Judge Contributors
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');
require_once($CFG->dirroot . '/mod/iajudge/lib.php');

$id = required_param('id', PARAM_INT); // Course ID.

$course = $DB->get_record('course', ['id' => $id], '*', MUST_EXIST);

require_login($course);

$PAGE->set_url('/mod/iajudge/index.php', ['id' => $id]);
$PAGE->set_title(format_string($course->shortname) . ': ' . get_string('modulenameplural', 'mod_iajudge'));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context(context_course::instance($course->id));

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('modulenameplural', 'mod_iajudge'));

// Retrieve all iajudge instances in this course.
$iajudges = get_all_instances_in_course('iajudge', $course);

if (empty($iajudges)) {
    notice(get_string('no_submissions', 'mod_iajudge'), new moodle_url('/course/view.php', ['id' => $course->id]));
}

// Build a table to list all instances.
$table            = new html_table();
$table->head      = [get_string('name'), get_string('description')];
$table->attributes = ['class' => 'generaltable mod_index'];

foreach ($iajudges as $iajudge) {
    $link = html_writer::link(
        new moodle_url('/mod/iajudge/view.php', ['id' => $iajudge->coursemodule]),
        format_string($iajudge->name, true)
    );

    $description = format_module_intro('iajudge', $iajudge, $iajudge->coursemodule, false);

    $table->data[] = [$link, $description];
}

echo html_writer::table($table);
echo $OUTPUT->footer();
