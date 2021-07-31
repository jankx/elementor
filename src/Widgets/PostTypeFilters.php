<?php
namespace Jankx\Elementor\Widgets;

use Elementor\Controls_Manager;
use Jankx\Elementor\WidgetBase;

class PostTypeFilters extends WidgetBase
{
    const WIDGET_NAME  = 'post_type_filter';

    public function get_name()
    {
        return static::WIDGET_NAME;
    }

    public function get_title()
    {
    }

    public function get_icon()
    {
        return 'atz-user';
    }

    protected function register_controls()
    {
        $this->start_controls_section(
            'content',
            array(
                'label' => __('Content', 'jankx'),
                'tab' => Controls_Manager::TAB_CONTENT,
            )
        );
        $this->end_controls_section();
    }

    protected function render()
    {
    }
}
