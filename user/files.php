<?php

// This file is part of Moodle - http://moodle.org/
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
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Manage files in folder in private area.
 *
 * @package   core_files
 * @copyright 2010 Petr Skoda (http://skodak.org)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../config.php');
require_once("$CFG->dirroot/user/files_form.php");
require_once("$CFG->dirroot/repository/lib.php");
require_once($CFG->dirroot.'/repository/elis_files/lib/lib.php'); // RL EDIT: BJB130215

require_login();
if (isguestuser()) {
    die();
}

// RL EDIT: BJB130215
$returnurl = optional_param('returnurl', '', PARAM_URL); // was: PARAM_LOCALURL
$courseid = optional_param('courseid', SITEID, PARAM_INT); // ELIS-7127
$returnbutton = true;
// End RL EDIT

if (empty($returnurl)) {
    $returnurl = new moodle_url('/user/files.php');
}

$context = context_user::instance($USER->id);
require_capability('moodle/user:manageownfiles', $context);

$title = get_string('myfiles');
$struser = get_string('user');

$PAGE->set_url('/user/files.php');
$PAGE->set_context($context);
$PAGE->set_title($title);
$PAGE->set_heading($title);
$PAGE->set_pagelayout('mydashboard');
$PAGE->set_pagetype('user-files');
// RL EDIT: BJB130215 - need this module for the rendering of the ELIS Files advanced search UI
// $PAGE->requires->yui_module(array('yui2-layout', 'yui2-container', 'yui2-dragdrop'), 'TBD_init_function');

// Obtain the UUID for the default browsing location
$currentpath = elis_files_get_current_path_for_course($courseid, true);
// End RL EDIT

$maxbytes = $CFG->userquota;
$maxareabytes = $CFG->userquota;
if (has_capability('moodle/user:ignoreuserquota', $context)) {
    $maxbytes = USER_CAN_IGNORE_FILE_SIZE_LIMITS;
    $maxareabytes = FILE_AREA_MAX_BYTES_UNLIMITED;
}

$data = new stdClass();
$data->returnurl = $returnurl;
$options = array('subdirs' => 1, 'maxbytes' => $maxbytes, 'maxfiles' => -1, 'accepted_types' => '*',
        'areamaxbytes' => $maxareabytes,
        'currentpath'  => $currentpath // RL EDIT: BJB130215
    );
file_prepare_standard_filemanager($data, 'files', $options, $context, 'user', 'private', 0);

$mform = new user_files_form(null, array('data'=>$data, 'options'=>$options));

if ($mform->is_cancelled()) {
    redirect($returnurl);
} else if ($formdata = $mform->get_data()) {
    $formdata = file_postupdate_standard_filemanager($formdata, 'files', $options, $context, 'user', 'private', 0);
    redirect($returnurl);
}

echo $OUTPUT->header();
echo $OUTPUT->box_start('generalbox');
$mform->display();
echo $OUTPUT->box_end();
echo $OUTPUT->footer();
