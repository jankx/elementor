<?php
if (!defined('ABSPATH')) {
    exit('Cheating huh?');
}
use Jankx\Elementor\Elementor;
use Elementor\Plugin;

define( 'JANKX_ELEMENTOR_ROOT_DIR', dirname(__FILE__) );

if (class_exists(Elementor::class) && class_exists(Plugin::class))
{
    $elementor = new Elementor();
    add_action(
        'after_setup_theme',
        array($elementor, 'integrate')
    );

    add_filter(
    	'jankx/template/loader/load',
    	array($elementor, 'loadTemplateInEditingMode')
    );
}
