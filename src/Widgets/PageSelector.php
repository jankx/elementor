<?php
namespace Jankx\Elementor\Widgets;

use Jankx;
use Elementor\Controls_Manager;
use Jankx\Elementor\WidgetBase;
use Jankx\PostLayout\PostLayoutManager;
use Jankx\PostLayout\Layout\Card;
use Jankx\PostLayout\Layout\Carousel;
use Jankx\PostLayout\Layout\Grid;
use Jankx\PostLayout\Layout\Preset5;
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

    protected function register_controls()
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

        $this->add_responsive_control(
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

        $this->add_responsive_control(
            'show_carousel_pagination',
            [
                'label' => __('Carousel Pagination', 'jankx'),
                'type' => Controls_Manager::SWITCHER,
                'label_on' => __('Show', 'jankx'),
                'label_off' => __('Hide', 'jankx'),
                'return_value' => 'yes',
                'default' => 'no'
            ]
        );

        $this->add_responsive_control(
            'show_carousel_nav',
            [
                'label' => __('Carousel Nav', 'jankx'),
                'type' => Controls_Manager::SWITCHER,
                'label_on' => __('Show', 'jankx'),
                'label_off' => __('Hide', 'jankx'),
                'return_value' => 'yes',
                'default' => 'no'
            ]
        );

        $this->add_responsive_control(
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

        $this->add_responsive_control(
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
                    'post_layout' => array(Carousel::LAYOUT_NAME, Preset5::LAYOUT_NAME),
                    'post_layout_mobile' => array(Carousel::LAYOUT_NAME, Preset5::LAYOUT_NAME),
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
            'style' => array_get($settings, 'item_style', 'simple'),
            'show_post_thumbnail' => array_get($settings, 'show_thumbnail'),
            'thumbnail_size' => array_get($settings, 'thumbnail_size', 'medium'),
            'columns_mobile' => array_get($settings, 'columns_mobile'),
            'columns_tablet' => array_get($settings, 'columns_tablet'),
            'columns' => $this->get_responsive_setting('columns', 4),
            'rows' => $this->get_responsive_setting('rows', 1),
            'layout' => $this->get_responsive_setting('post_layout', Card::LAYOUT_NAME),
            'show_dot'  => $this->get_responsive_setting('show_carousel_pagination', 'no') === 'yes',
            'show_nav'  => $this->get_responsive_setting('show_carousel_nav', 'no') === 'yes',
        ));

        echo $postsRenderer->render();
    }
}
