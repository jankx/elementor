<?php

namespace Jankx\Elementor\Widgets;

if (!defined('ABSPATH')) {
    exit('Cheating huh?');
}

use Elementor\Widget_Heading;

class GradientHeading extends Widget_Heading
{
    public function get_name()
    {
        return 'gradient_heading';
    }

    public function get_title()
    {
        return __('Gradient Heading', 'jankx');
    }
}
