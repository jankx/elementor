<?php
use Jankx\Elementor\Elementor;

define( 'JANKX_ELEMENTOR_ROOT_DIR', dirname(__FILE__) );

if (class_exists(Elementor::class))
{
    $elementor = new Elementor();
    add_action(
        'after_setup_theme',
        array($elementor, 'integrate')
    );
}
