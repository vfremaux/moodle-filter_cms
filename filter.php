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
 * Basic content replacement with cms page.
 *
 * @package    filter
 * @subpackage cms
 * @copyright  2015 Valery Fremaux (valery@edunao.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/local/cms/locallib.php');

/**
 * This class looks for cms placeholders {cmspage <ID>}
 * and replaces the placeholder with the page's content
 */
class filter_cms extends moodle_text_filter {

    function filter($text, array $options = array()) {
    /// Do a quick check using stripos to avoid unnecessary work
        if (strpos($text, '{') === false) {
            return $text;
        }

    /// There might be an email in here somewhere so continue ...
        $matches = array();

    /// regular expression to define a cmspage placeholder.
        $cmspageregex = '/\{cmspage\s+?(\d+)\}/';

    /// pattern to find a cms placeholder with the page content text.
        $text = preg_replace_callback($cmspageregex, 'filter_cms_page_replace', $text);

        return $text;
    }
}


function filter_cms_page_replace($matches) {
    global $PAGE, $COURSE;

    if (is_numeric($matches[1])) {

        $renderer = $PAGE->get_renderer('local_cms');

        $pagedata = cms_get_page_data_by_id(null, $matches[1]);
        $pagedata->nid = $pagedata->naviid;
        if ($pagedata->publish) {
            if ($COURSE->id > SITEID && $pagedata->course > SITEID) {
                if ($COURSE->id != $pagedata->course) {
                    return '';
                }
            }
            return $renderer->render_page($pagedata, $COURSE);
        }
    }

    return '';
}


