<?php
namespace Jankx\Elementor\Widgets;

use Jankx;
use Elementor\Controls_Manager;
use Jankx\Elementor\WidgetBase;
use Jankx\PostLayout\PostLayoutManager;
use Jankx\PostLayout\Layout\Card;
use Jankx\PostLayout\Layout\Carousel;
use Jankx\PostLayout\Layout\Grid;
use Jankx\Widget\Renderers\PageSelectorRenderer;
use AdvancedElementor\Controls\Choices;

class PageSelector extends WidgetBase
{
    public function get_name()
    {
        return 'page_selector';
    }

    public function get_title()
    {
        return sprintf(
            '%s %s',
            Jankx::templateName(),
            __('Page Selector', 'jankx')
        );
    }

    public function get_icon()
    {
        return 'eicon-checkbox';
    }

    public function get_categories()
    {
        return array(
            'theme-elements',
            Jankx::templateStylesheet()
        );
    }

    protected function _register_controls()
    {
        $page_options = $this->get_page_options('item');

        $this->start_controls_section(
            'content_section',
            array(
                'label' => __('Content', 'jankx_ecommerce'),
                'tab' => Controls_Manager::TAB_CONTENT,
            )
        );

        $this->add_control('selected_pages', [
            'label' => __('Pages', 'jankx'),
            'description' => __('Choose your page want to show', 'jankx'),
            'type' => Choices::CONTROL_NAME,
            'options' => $page_options,
            'multiple' => true,
            'default' => array(),
        ]);

        $this->add_control(
            'post_layout',
            [
                'label' => __('Layout', 'jankx'),
                'type' => Controls_Manager::SELECT,
                'default' => Card::LAYOUT_NAME,

                'options' => PostLayoutManager::getLayouts(array(
                    'field' => 'names',
                )),
            ]
        );

        $this->add_control(
            'item_style',
            [
                'label' => __('Style', 'jankx'),
                'type' => Controls_Manager::SELECT,
                'default' => 'simple',
                'options' => PageSelectorRenderer::getStyleSupports(),
            ]
        );

        $this->addThumbnailControls();

        $this->add_control(
            'columns',
            [
                'label' => __('Columns', 'jankx'),
                'type' => Controls_Manager::NUMBER,
                'min' => 1,
                'max' => 10,
                'step' => 1,
                'default' => 4,
                'of_type' => 'post_layout',
                'condition' => array(
                    'post_layout' => array(Card::LAYOUT_NAME, Carousel::LAYOUT_NAME, Grid::LAYOUT_NAME)
                )
            ]
        );

        $this->add_control(
            'rows',
            [
                'label' => __('Rows', 'jankx'),
                'type' => Controls_Manager::NUMBER,
                'min' => 1,
                'max' => 10,
                'step' => 1,
                'default' => 1,
                'of_type' => 'post_layout',
                'condition' => array(
                    'post_layout' => array(Carousel::LAYOUT_NAME)
                )
            ]
        );

        $this->end_controls_section();
    }

    public function render()
    {
        $settings      = $this->get_settings_for_display();

        $postsRenderer = PageSelectorRenderer::prepare(array(
            'pages' => array_get($settings, 'selected_pages', []),
            'layout' => array_get($settings, 'post_layout', Card::LAYOUT_NAME),
            'style' => array_get($settings, 'item_style', 'simple'),
            'columns' => array_get($settings, 'columns', 4),
            'rows' => array_get($settings, 'rows', 1),
            'show_post_thumbnail' => array($settings, 'show_thumbnail'),
            'thumbnail_size' => array($settings, 'thumbnail_size', 'medium'),
        ));

        echo $postsRenderer->render();
    }
}
