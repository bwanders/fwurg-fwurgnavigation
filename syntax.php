<?php
/**
 * DokuWiki Plugin stratastorage (Syntax Component)
 *
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 * @author  Brend Wanders <b.wanders@utwente.nl>
 */

// must be run within Dokuwiki
if (!defined('DOKU_INC')) die('Meh.');

/**
 * Simple plugin that sets the 'no data' flag.
 */
class syntax_plugin_fwurgnavigation extends DokuWiki_Syntax_Plugin {
    public function __construct() {
        $this->templatery =& plugin_load('helper', 'templatery');
    }

    public function getType() {
        return 'substition';
    }

    public function getPType() {
        return 'normal';
    }

    public function getSort() {
        // sort at same level as notoc
        return 30;
    }

    public function connectTo($mode) {
        $this->Lexer->addSpecialPattern('\{\{navigation>[^}]+?}}',$mode,'plugin_fwurgnavigation');
    }

    public function handle($match, $state, $pos, &$handler){
        preg_match('/\{\{navigation>([^\}|]+?)(?:\|([^}]+?))?}}/msS',$match,$capture);
        $id = $capture[1];
        $vars = $capture[2];

        // parse variables 
        $variables = array();
        $vars = explode('|', $vars);
        $j = 0;
        for($i=0;$i<count($vars);$i++) {
            if(trim($vars[$i])=='') continue;
            if(preg_match('/^(.+?)=(.*)$/',$vars[$i],$capture)) {
                $variables[$capture[1]] = $capture[2];
            } else {
                $variables[$j++] = $vars[$i];
            }
        }

        return array($id, $variables);
    }

    public function render($mode, &$R, $data) {
        global $ID;

        if($mode == 'metadata') {
            $variables = $data[1];
    
            // try to see if we need to replace values
            if($this->templatery->isDelegating()) {
                $d = $this->templatery->getDelegate();
                foreach($variables as $key=>$value) {
                    if(preg_match('/^@@(.+)@@$/',$value,$m)) {
                        $variables[$key] = $d->getField($mode, $R, $m[1]);
                    }
                }
            }
    
            $base = $this->getConf('navigation_namespace');
            $id = $data[0];

            list($page, $hash) = explode('#',$id,2);
            if(empty($hash)) $hash = '';
            $hash = $this->templatery->cleanTemplateId($hash);
            resolve_pageid($base, $page, $exists);

            $R->meta['fwurgnavigation'][] = array(
                'template'=>array(
                    'page'=>$page, 
                    'hash'=>$hash
                ),
                'variables'=>$variables
            );

            return true;
        }

        return false;
    }
}

