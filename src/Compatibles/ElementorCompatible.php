<?php

namespace Jankx\Elementor\Compatibles;

if (!defined('ABSPATH')) {
    exit('Cheating huh?');
}

use Elementor\Widget_Base;
use Elementor\Widgets_Manager;

class ElementorCompatible
{
    public static function compareVersion($version)
    {
        return version_compare($version, ELEMENTOR_VERSION, "<=");
    }

    public static function registerControl($widgetManager)
    {
    }

    public static function registerWidget(Widgets_Manager $widgetManager, Widget_Base $widget)
    {
        $method = static::compareVersion('3.5.0') ? 'register' : 'register_widget_type';
        $args   = [$widget];
        return call_user_func_array([$widgetManager, $method], $args);
    }
}
