<?php

namespace Jankx\Elementor\Widgets;

use Jankx;
use Elementor\Controls_Manager;
use Elementor\Utils;
use Jankx\Elementor\WidgetBase;
use Jankx\Widget\Renderers\SocialSharingRenderer;

class SocialSharing extends WidgetBase
{
    public function get_name()
    {
        return 'social_sharing';
    }

    public function get_title()
    {
        return sprintf(
            '%s %s',
            Jankx::templateName(),
            __('Social Sharing', 'jankx')
        );
    }

    public function get_categories()
    {
        return array(
            'theme-elements',
            Jankx::templateStylesheet()
        );
    }

    public function get_icon()
    {
        return 'eicon-share-arrow';
    }

    protected function register_controls()
    {
        $this->start_controls_section('content', [
            'label' => __('Content', 'jankx'),
            'tab' => Controls_Manager::TAB_CONTENT
        ]);

        $this->add_control('widget_title', array(
            'label' => __('Widget Title', 'jankx'),
            'type' => Controls_Manager::TEXT,
            'default' => '',
        ));

        $this->add_control(
            'header_size',
            [
                'label' => __('HTML Tag', 'elementor'),
                'type' => Controls_Manager::SELECT,
                'options' => [
                    'h1' => 'H1',
                    'h2' => 'H2',
                    'h3' => 'H3',
                    'h4' => 'H4',
                    'h5' => 'H5',
                    'h6' => 'H6',
                    'div' => 'div',
                    'span' => 'span',
                    'p' => 'p',
                ],
                'default' => 'h3',
            ]
        );

        $this->end_controls_section();
    }

    public function render()
    {
        $settings = $this->get_settings_for_display();
        $widget_title = trim(array_get($settings, 'widget_title', ''));
        if ($widget_title) {
            $title_html = sprintf(
                '<%1$s %2$s>%3$s</%1$s>',
                Utils::validate_html_tag($settings['header_size']),
                $this->get_render_attribute_string('title'),
                $widget_title
            );
            echo $title_html;
        }
        $renderer = new SocialSharingRenderer();

        echo $renderer->render();
    }
}
