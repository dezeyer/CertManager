<?php
/* Smarty-Dateien laden */
require_once('Smarty.class.php');


/* An dieser Stelle können Applikations spezifische Bibliotheken geladen werden
* Bsp: require(bib.lib.php);
* */

$smtemplate_config =
    array(
        'layouts_dir' => 'inc/layouts',
        'template_dir' => 'inc/views/',
        'compile_dir' => 'libs/smarty/templates_c/',
        'cache_dir' => 'libs/smarty/cache/',
        'config_dir' => 'libs/smarty/config/',
    );

class SMTemplate extends Smarty {
    function __construct(){
        parent::__construct();

        global $smtemplate_config;
        //$this->template_dir = $smtemplate_config['template_dir'];
        $this->setTemplateDir(array(
            'one' => $smtemplate_config['template_dir'],
            'two' => $smtemplate_config['layouts_dir'],
        ));
        //$this->addTemplateDir(array('two' => $smtemplate_config['layouts_dir']));
        $this->compile_dir = $smtemplate_config['compile_dir'];
        $this->cache_dir = $smtemplate_config['cache_dir'];
        $this->config_dir = $smtemplate_config['config_dir'];
    }
    function render($template, $data = array(), $layout = 'page',$title = "") {
        foreach($data as $key => $value){
            $this->assign($key, $value);
        }
        $this->assign("title", $title);
        $content = $this->fetch($template . '.tpl');
        $this->assign('__content', $content);

        $this->display($layout . '.tpl');
    }
}