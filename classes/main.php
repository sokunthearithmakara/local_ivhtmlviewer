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

namespace local_ivhtmlviewer;

/**
 * Class main
 *
 * @package    local_ivhtmlviewer
 * @copyright  2024 Sokunthearith Makara <sokunthearithmakara@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class main extends \ivplugin_richtext\main {
    /**
     * Get the property.
     */
    public function get_property() {
        return [
            'name' => 'htmlviewer',
            'icon' => 'bi bi-filetype-html',
            'title' => get_string('htmlviewercontent', 'local_ivhtmlviewer'),
            'amdmodule' => 'local_ivhtmlviewer/main',
            'class' => 'local_ivhtmlviewer\\main',
            'form' => 'local_ivhtmlviewer\\form',
            'hascompletion' => true,
            'hastimestamp' => true,
            'hasreport' => true,
            'description' => get_string('htmlviewerdescription', 'local_ivhtmlviewer'),
            'author' => 'tsmakara',
            'authorlink' => 'mailto:sokunthearithmakara@gmail.com',
            'tutorial' => get_string('tutorialurl', 'local_ivhtmlviewer'),
            'preloadstrings' => false,
            'flexbook' => true,
            'fbdescription' => get_string('fbdescription', 'local_ivhtmlviewer'),
            'fbamdmodule' => 'local_ivhtmlviewer/fbmain',
            'fbform' => 'local_ivhtmlviewer\\fbform',
            'dndextensions' => ['html', 'htm'],
            'component' => 'local_ivhtmlviewer',
        ];
    }

    /**
     * Create a new interaction instance.
     *
     * @param array $data The data for the new instance.
     * @return \stdClass The newly created interaction record.
     */
    public function create_instance($data) {
        global $DB, $CFG;
        $data = (object) $data;
        $draftitemid = $data->draftitemid;
        unset($data->draftitemid);

        // Form a default advanced settings.
        if (empty($data->advanced)) {
            $data->advanced = $this->flexbook_advanced();
            $data->advanced = json_encode($data->advanced);
            $data->completiontracking = 'none';
            $data->xp = 0;
        }

        $data->id = $DB->insert_record('flexbook_items', $data);

        // Save files from draft area.
        if ($draftitemid) {
            require_once($CFG->libdir . '/filelib.php');
            \file_save_draft_area_files(
                $draftitemid,
                $data->contextid,
                'mod_flexbook',
                'content',
                $data->id,
                ['subdirs' => 0, 'maxfiles' => 1]
            );
        }

        return \mod_flexbook\util::get_item($data->id, $data->contextid);
    }

    /**
     * Get the content.
     *
     * @param array $arg The arguments.
     * @return string The content.
     */
    public function get_content($arg) {
        $iframeurl = helper::normalize_iframeurl($arg['iframeurl'] ?? '');
        if (!empty($iframeurl)) {
            return '<iframe id="iframe" src="' . s($iframeurl) .
                '" style="width: 100%" frameborder="0" allow="autoplay" class="iv-rounded-0"></iframe>';
        }

        $plugin = 'mod_' . (isset($arg['plugin']) ? $arg['plugin'] : 'interactivevideo');
        $fs = get_file_storage();
        $files = $fs->get_area_files($arg["contextid"], $plugin, 'content', $arg["id"], 'id DESC', false);
        $file = reset($files);
        if ($file) {
            $url = \moodle_url::make_pluginfile_url(
                $file->get_contextid(),
                $file->get_component(),
                $file->get_filearea(),
                $file->get_itemid(),
                $file->get_filepath(),
                $file->get_filename()
            )->out();
            return '<iframe id="iframe" src="' . $url .
                '" style="width: 100%" frameborder="0" allow="autoplay" class="iv-rounded-0"></iframe>';
        }
        return 'No content found';
    }
}
