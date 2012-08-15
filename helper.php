<?php
/**
 * DokuWiki Plugin fwurgnavigation (Helper Component)
 *
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 * @author  Brend Wanders <b.wanders@utwente.nl>
 */

if (!defined('DOKU_INC')) die('meh.');

/**
 * Helper plugin for common syntax parsing.
 */
class helper_plugin_fwurgnavigation extends DokuWiki_Plugin {
    function helper_plugin_fwurgnavigation() {
        $this->types =& plugin_load('helper', 'stratastorage_types');
        $this->triples =& plugin_load('helper', 'stratastorage_triples', false);
        $this->triples->initialize();

        $this->templatery =& plugin_load('helper', 'templatery');
    }

    function tpl() {
        global $ID;

        // get navigation boxes
        $navigations = p_get_metadata($ID, 'fwurgnavigation');
        if(is_null($navigations) || empty($navigations)) return;

        // set up renderer
        $R =& p_get_renderer('xhtml');
        if(is_null($R)) return;

        $R->smileys = getSmileys();
        $R->entities = getEntities();
        $R->acronyms = getAcronyms();
        $R->interwiki = getInterwiki();

        // process each navigation box
        foreach($navigations as $navigation) {
            $variables = array();
            foreach($navigation['variables'] as $key=>$value) {
                $variables[$key] = array($value);
            }
    
            $handler = new stratatemplatery_handler(
                $variables,
                $this->types,
                $this->triples,
                array()
            );

            $box =& $this->templatery->loadTemplate($navigation['template']['page'], $navigation['template']['hash']);
            if($box == null) continue;

            $this->templatery->applyTemplate($box, $handler, $R);
        }

        $data = array('xhtml', & $R->doc);
        trigger_event('RENDERER_CONTENT_POSTPROCESS', $data);

        echo $R->doc;
    }
}