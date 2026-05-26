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
 * HTML content viewer flexbook module.
 *
 * @module     local_ivhtmlviewer/fbmain
 * @copyright  2026 Sokunthearith Makara <sokunthearithmakara@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
import Iframe from 'ivplugin_iframe/fbmain';
import Ajax from 'core/ajax';
import {safeParse} from 'mod_flexbook/utils';

export default class HtmlViewer extends Iframe {
    /**
     * Called when the edit form is loaded.
     * @return {void}
     */
    onEditFormLoaded() {
        // Do nothing.
    }

    /**
     * Override postContentRender to clean up loading background.
     *
     * @param {Object} annotation
     * @param {jQuery} $message
     */
    postContentRender(annotation, $message) {
        super.postContentRender(annotation, $message);

        const checkIframe = () => {
            const $iframe = $message.find('iframe');
            if ($iframe.length > 0) {
                // Remove the loading background because some iframe has transparent content.
                setTimeout(() => {
                    $iframe.css('background', 'none');
                }, 1000);
            } else {
                requestAnimationFrame(checkIframe);
            }
        };
        requestAnimationFrame(checkIframe);
    }

    /**
     * Handle drag and drop creation.
     *
     * @param {Array} annotations
     * @param {File} file
     * @param {Object} response
     * @param {number} anchorid
     */
    async dnd(annotations, file, response, anchorid = 0) {
        const result = await Ajax.call([{
            methodname: 'mod_flexbook_create_interaction',
            args: {
                contextid: M.cfg.contextid,
                courseid: this.course,
                cmid: this.cm,
                annotationid: this.flexbook,
                type: this.prop.name,
                title: file.name.replace(/\.[^/.]+$/, ""),
                draftitemid: response.draftitemid || 0,
                anchorid: anchorid
            }
        }])[0];

        const newItem = safeParse(result.data, {});
        this.dispatchEvent('annotationupdated', {
            annotation: newItem,
            action: 'add',
            anchorid: anchorid,
            isDnD: true
        });
    }
}
