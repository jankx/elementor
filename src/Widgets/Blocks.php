<?php

namespace Jankx\Elementor\Widgets;

if (!defined('ABSPATH')) {
    exit('Cheatin huh?');
}

use Elementor\Controls_Manager;
use Jankx;
use Elementor\Plugin as Elementor;
use Jankx\Elementor\WidgetBase;
use Jankx\Blocks\PostType as BlockPostType;

class Blocks extends WidgetBase
{
    const WIDGET_NAME = 'jankx_blocks';

    public function get_name()
    {
        return static::WIDGET_NAME;
    }

    public function get_title()
    {
        return sprintf(
            __('%s Blocks', 'jankx'),
            Jankx::templateName()
        );
    }

    public function get_icon()
    {
        return 'eicon-parallax';
    }

    public function get_categories()
    {
        return array(Jankx::templateStylesheet(), 'site');
    }

    protected function render()
    {
        $settings = $this->get_settings_for_display();

        $choiced_block = array_get($settings, 'jankx_block');
        if ($choiced_block) {
            $elementor = Elementor::instance();

            echo $elementor->frontend->get_builder_content_for_display(intval($choiced_block));
        }
    }

    protected function getAllBlocks()
    {
        $blocks = get_posts(array(
            'fields' => 'id=>name',
            'post_type' => BlockPostType::BLOCK_POST_TYPE,
            'posts_per_page' => -1,
        ));
        $ret = array();
        foreach ($blocks as $block) {
            $ret[$block->ID] = $block->post_title;
        }

        return $ret;
    }

    protected function register_controls()
    {
        $this->start_controls_section(
            'content_section',
            [
                'label' => __('Content', 'jankx'),
                'tab' => Controls_Manager::TAB_CONTENT,
            ]
        );
        $this->add_control(
            'widget_title',
            [
                'label' => __('Title', 'jankx'),
                'type' => Controls_Manager::TEXTAREA,
                'default' => __('Recent Posts', 'jankx'),
                'placeholder' => __('Input widget title', 'jankx'),
            ]
        );

        $this->add_control(
            'jankx_block',
            [
                'label' => __('Block', 'jankx'),
                'type' => Controls_Manager::SELECT,
                'options' => $this->getAllBlocks(),
            ]
        );

        $this->end_controls_section();
    }
}
