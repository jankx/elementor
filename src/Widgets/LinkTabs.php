<?php

namespace Jankx\Elementor\Widgets;

if (!defined('ABSPATH')) {
    exit('Cheating huh?');
}

use Elementor\Controls_Manager;
use Elementor\Repeater;
use Jankx\Widget\Renderers\LinkTabsRenderer;
use Jankx\Elementor\WidgetBase;
use Jankx\Elementor\Transformers\TransformSettingsToLinkTabs;

class LinkTabs extends WidgetBase
{
    public function get_name()
    {
        return 'link_tabs';
    }

    public function get_title()
    {
        return __('Link Tabs', 'jankx');
    }

    protected function register_controls()
    {
        $this->start_controls_section('content');
        $repeaterLinkTabControl = new Repeater();
        $repeaterLinkTabControl->add_control('tab_title', array(
            'type' => Controls_Manager::TEXT,
            'label' => __('Title'),
            'default' => '',
        ));
        $repeaterLinkTabControl->add_control('tab_link', array(
            'type' => Controls_Manager::URL,
            'label' => __('URL')
        ));

        $this->add_control('tabs', array(
            'label' => __('Tabs', 'jankx'),
            'type' => Controls_Manager::REPEATER,
            'fields' => $repeaterLinkTabControl->get_controls(),
        ));
        $this->end_controls_section();
    }

    public function render()
    {
        $settings = $this->get_settings_for_display();

        $renderer = LinkTabsRenderer::prepare(array(
            'tabs' => new TransformSettingsToLinkTabs(array_get($settings, 'tabs')),
        ));

        echo $renderer->render();
    }
}
