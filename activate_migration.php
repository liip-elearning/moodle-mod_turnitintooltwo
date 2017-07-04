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
 * @package   turnitintooltwo
 * @copyright 2012 iParadigms LLC
 */

require_once(__DIR__.'/../../config.php');
require_once($CFG->libdir.'/tablelib.php');
require_once(__DIR__.'/lib.php');
require_once($CFG->libdir.'/adminlib.php');
require_once(__DIR__."/turnitintooltwo_view.class.php");

admin_externalpage_setup('managemodules');

/**
 * activate_migration
 * Updates the database to flag that the user has enabled the migration tool.
 * @return object $activation - db record of the migration activation row inserted into config_plugins.
 */
function activate_migration() {
    global $DB, $CFG;
    $migration_enabled_params = array(
        'plugin' => 'turnitintooltwo',
        'name' => 'migration_enabled'
    );
    $migration_enabled = $DB->get_record('config_plugins', $migration_enabled_params);

    $activation_properties = new stdClass;
    $activation_properties->plugin = 'turnitintooltwo';
    $activation_properties->name = 'migration_enabled';
    $activation_properties->value  = 1;

    if (empty($migration_enabled)) {
        $activation = $DB->insert_record('config_plugins', $activation_properties);
    } else {
        $id = $migration_enabled->id;
        $activation = $DB->update_record('config_plugins', array('id' => $id, 'value' => 1));
    }

    return $activation;
}

/**
 * build_page
 * Builds the visual page for activate_migration
 * @return string $output
 */
function build_page() {
    global $CFG, $OUTPUT;

    $notice = html_writer::tag(
        'div',
        get_string('activatemigrationnotice', 'turnitintooltwo'),
        array('class'=>'alert alert-info')
    );
    $button = html_writer::link(
        new moodle_url('/mod/turnitintooltwo/activate_migration.php', array('do_migration' => 1)),
        get_string('activatemigration', 'turnitintooltwo'),
        array('class'=>'btn btn-default', 'role' => 'button')
    );

    $output = $OUTPUT->header();
    $output .= html_writer::start_tag('div', array('class' => 'mod_turnitintooltwo'));
    $output .= $OUTPUT->heading(get_string('pluginname', 'turnitintooltwo'), 2, 'main');
    $output .= $notice;
    $output .= $button;
    $output .= html_writer::end_tag("div");

    return $output;
}

// Discern whether the page is displaying its visual side, or doing its back-end work.
$do_migration = optional_param('do_migration', 0, PARAM_INT);

if ($do_migration) {
    $activation = activate_migration();
    $urlparams = array('section' => 'modsettingturnitintooltwo');
    if ($activation) {
        $urlparams['activation'] = 'success';
    } else {
        $urlparams['activation'] = 'failure';
    }
    redirect(new moodle_url('/admin/settings.php', $urlparams));
} else {
    echo build_page();
}
