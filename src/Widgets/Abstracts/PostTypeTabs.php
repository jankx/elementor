<?php

namespace Jankx\Elementor\Widgets\Abstracts;

if (!defined('ABSPATH')) {
    exit('Cheatin huh?');
}

use Jankx;
use Elementor\Controls_Manager;
use Jankx\Elementor\WidgetBase;
use Jankx\Widget\Renderers\PostsTabsRenderer;
use Jankx\PostLayout\Layout\Card;
use AdvancedElementor\Controls\Choices;
use Jankx\PostLayout\PostLayoutManager;
use Jankx\Elementor\Traits\FilterDataTrait;

abstract class PostTypeTabs extends WidgetBase
{
    use FilterDataTrait;

    protected $postType = 'post';
    protected $filters = array();

    public function __construct($data = array(), $args = null)
    {
        parent::__construct($data, $args);
        $this->createTabs();
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
        return 'eicon-tabs';
    }

    public function get_post_types()
    {
        return 'post';
    }

    abstract protected function createTabs();

    protected function register_controls()
    {
        $this->start_controls_section('content', array(
            'label' => __('Content'),
            'tab' => Controls_Manager::TAB_CONTENT,
        ));


        foreach (apply_filters("jankx/elementor/{$this->get_name()}/filters", $this->filters) as $filter => $args) {
            $this->add_control(
                $filter,
                [
                    'label' => array_get($args, 'label'),
                    'type' => Choices::CONTROL_NAME,
                    'default' => array(),
                    'options' => apply_filters(
                        'jankx/elementor/post/tabs/' . $filter,
                        $this->getFilterOptions($args, $filter)
                    ),
                    'multiple' => true,
                ]
            );
        }

        $this->registerExtraControls();

        do_action("jankx/elementor/widget/{$this->get_name()}/filters/after", $this);

        $this->add_control('post_layout', array(
            'label' => __('Layout', 'jankx'),
            'type' => Controls_Manager::SELECT,
            'options' => PostLayoutManager::getLayouts(array(
                'field' => 'names',
                'exclude' => 'parent'
            )),
            'default' => Card::LAYOUT_NAME,
        ));


        $this->add_control('columns', array(
            'label' => __('Columns', 'jankx'),
            'type' => Controls_Manager::NUMBER,
            'default' => 4,
        ));

        $this->add_control('limit', array(
            'label' => __('Limit', 'jankx'),
            'type' => Controls_Manager::NUMBER,
            'default' => 10,
        ));

        $this->end_controls_section();

        do_action("jankx/elementor/widget/{$this->get_name()}/section/content/after", $this);
    }

    protected function transformTaxonomyTabs($setting_id, $data, &$tabs)
    {
        if (empty($data['taxonomy'])) {
            return false;
        }


        $settings = $this->get_settings_for_display();
        $args = array(
            'taxonomy' => $data['taxonomy'],
            'hide_empty' => false,
        );

        if (!in_array('all', array_get($settings, $setting_id, []))) {
            $args['include'] = array_get($settings, $setting_id, []);
            $args['orderby'] = 'include';
            $args['order'] = 'ASC';
        }

        $terms = get_terms($args);
        foreach ($terms as $term) {
            $tabs[] = array(
                'title' => $term->name,
                'object' => array(
                    'type' => 'taxonomy',
                    'type_name' => $term->taxonomy,
                    'id' => $term->term_id,
                ),
                'url' => get_term_link($term)
            );
        }
    }

    protected function transformPostMetaTabs($setting_id, $data, &$tabs)
    {
        if (empty($data['meta_key'])) {
            return false;
        }

        $settings = $this->get_settings_for_display();
        $setting_metas = array_get($settings, $setting_id);
        $post_metas = array_get($data, 'options');
        $selected_metas = array_filter($post_metas, function ($meta) use ($setting_metas) {
            return in_array($meta, $setting_metas) || in_array('all', $setting_metas);
        });

        foreach ($selected_metas as $meta_value => $meta_display) {
            $tabs[] = array(
                'title' => $meta_display,
                'object' => array(
                    'type' => 'post_meta',
                    'type_name' => array_get($data, 'meta_key'),
                    'id' => array_get($data, 'meta_key')
                )
            );
        }
    }

    protected function transformElementorSettingsToTabs()
    {
        $tabs = array();

        foreach ($this->filters as $filter_name => $filter_args) {
            if (!$filter_args['type']) {
                continue;
            }
            switch ($filter_args['type']) {
                case 'taxonomy':
                    $this->transformTaxonomyTabs($filter_name, $filter_args, $tabs);
                    break;
                case 'post_meta':
                    $this->transformPostMetaTabs($filter_name, $filter_args, $tabs);
                    break;
            }
        }

        return $tabs;
    }

    protected function preparingRenderLayout($layoutOptions)
    {
        do_action_ref_array("jankx/elementor/widget/{$this->get_name()}/layout/options/prepare", [
            &$layoutOptions
        ]);

        return $layoutOptions;
    }

    protected function render()
    {
        $settings = $this->get_settings_for_display();
        $renderer = PostsTabsRenderer::prepare(array(
            'layout' => array_get($settings, 'post_layout', Card::LAYOUT_NAME),
            'tabs' => $this->transformElementorSettingsToTabs(),
            'post_type' => $this->get_post_types(),
            'posts_per_page' => array_get($settings, 'limit', 10)
        ));
        $renderer->setLayoutOptions(
            $this->preparingRenderLayout(['columns' => array_get($settings, 'columns', 4)])
        );

        do_action("jankx/{$this->postType}/tabs/render/init", $this, $renderer);
        echo $renderer->render();
        do_action("jankx/{$this->postType}/tabs/render/end", $this, $renderer);
    }
}
