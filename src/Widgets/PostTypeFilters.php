<?php
namespace Jankx\Elementor\Widgets;

use Elementor\Controls_Manager;
use Jankx\Elementor\WidgetBase;
use Jankx\Widget\Renderers\PostTypeFiltersRenderer;

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
        $settings = $this->get_settings_for_display();
        $filters = new PostTypeFiltersRenderer();
        $filters->setOptions(array(
            'layout' => array_get($settings, 'layout', 'card'),
            'posts_per_page' => array_get($settings, 'limit', 10),
        ));
        $filters->setLayoutOptions(array(
            'columns' => array_get($settings, 'columns', 4),
        ));

        echo $filters->render();
    }
}
