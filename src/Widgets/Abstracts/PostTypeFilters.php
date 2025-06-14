<?php

namespace Jankx\Elementor\Widgets\Abstracts;

if (!defined('ABSPATH')) {
    exit('Cheatin huh?');
}

use Jankx;
use Elementor\Controls_Manager;
use AdvancedElementor\Controls\Choices;
use Jankx\Elementor\Traits\FilterDataTrait;
use ELementor\Widget_Base;
use Jankx\Elementor\WidgetBase;
use Jankx\Filter\FilterManager;
use Jankx\Filter\Renderer\PostTypeFiltersRenderer;
use Jankx\Filter\Transformer\ArraysToFilterDataTransformer;
use Jankx\PostLayout\Layout\Card;
use Jankx\PostLayout\PostLayoutManager;

abstract class PostTypeFilters extends WidgetBase
{
    use FilterDataTrait;

    protected $filters = array();
    protected $instanceId = 0;
    protected $filterManager;
    protected static $instanceIds = array();

    public function __construct($data = array(), $args = null)
    {
        Widget_Base::__construct($data, $args);
        $this->filters = apply_filters(
            "jankx/elementor/{$this->get_name()}/filters",
            $this->filters()
        );
        $this->instanceId = empty(static::$instanceIds) ? 1 : max(static::$instanceIds);
        $this->filterManager = FilterManager::getInstance();
    }

    public function get_icon()
    {
        return 'eicon-filter';
    }

    public function get_categories()
    {
        return array(
            'theme-elements',
            Jankx::templateStylesheet()
        );
    }

    protected function getActiveFilters($includeAll = false)
    {
        $filters = $includeAll ? array('all' => __('All')) : array();

        $filters = $filters + array_map(function ($filter) {
            return $filter['label'];
        }, $this->filters);

        return $filters;
    }

    protected function register_controls()
    {
        $filterStyles = array_map(
            function ($filterStyle) {
                return $filterStyle->getTitle();
            },
            $this->filterManager->getFilterStyles()
        );
        $defaultFilterStyle = empty($filterStyles) ? '' : array_keys($filterStyles)[0];

        $this->start_controls_section(
            'content',
            array(
                'label' => __('Content', 'jankx'),
                'tab' => Controls_Manager::TAB_CONTENT,
            )
        );

        if (!empty($this->filters)) {
            $this->add_control('active_filters', [
                'label' => __('Filters', 'jankx'),
                'type' => Choices::CONTROL_NAME,
                'options' => $this->getActiveFilters(true),
                'default' => $this->defaultFilters(),
                'multiple' => true,
            ]);
        }

        foreach (apply_filters("jankx/elementor/{$this->get_name()}/filters", $this->filters) as $filter => $args) {
            $this->add_control(
                $filter . '_filter',
                [
                    'label' => array_get($args, 'label'),
                    'type' => Choices::CONTROL_NAME,
                    'default' => array(),
                    'options' => apply_filters(
                        'jankx/elementor/post/filters/' . $filter,
                        $this->getFilterOptions($args, $filter)
                    ),
                    'multiple' => true,
                ]
            );
        }

        $this->add_control('filter_style', [
            'label' => __('Filter Style', 'jankx_filter'),
            'type' => Controls_Manager::SELECT,
            'options' => $filterStyles,
            'default' => $defaultFilterStyle,
        ]);

        $this->registerExtraControls();

        $this->add_control('post_layout', [
            'label' => __('Post Layout', 'jankx'),
            'type' => Controls_Manager::SELECT,
            'options' => PostLayoutManager::getLayouts(array(
                'field' => 'names'
            )),
            'default' => Card::LAYOUT_NAME
        ]);

        $this->add_control('columns', [
            'label' => __('Columns', 'jankx'),
            'type' => Controls_Manager::NUMBER,
            'default' => 4,
        ]);
        $this->add_control('limit', [
            'label' => __('Limit', 'jankx'),
            'type' => Controls_Manager::NUMBER,
            'default' => 10,
        ]);

        $this->end_controls_section();
    }

    abstract protected function filters();

    protected function defaultFilters()
    {
        return ['all'];
    }

    protected function transformTaxonomyFilterData($filter, $options, &$transformer)
    {
        if (!isset($filter['taxonomy'])) {
            return;
        }
        $filter['id'] = $filter['taxonomy'];
        $args = array(
            'taxonomy' => $filter['taxonomy'],
            'hide_empty' => false,
        );
        if (in_array('all', $options)) {
            $args['num'] = -1;
        } else {
            $args['include'] = array_map(function ($option) {
                return preg_replace('/[^\d]/', '', $option);
            }, $options);
            $args['orderby'] = 'include';
            $args['order'] = 'ASC';
        }
        $terms = get_terms($args);
        foreach ($terms as $term) {
            $filter['options'][] = apply_filters('jankx/filter/data/term', array(
                'label' => $term->name,
                'id' => $term->term_id,
                'url' => get_term_link($term, $term->taxonomy),
            ), $term, $filter);
        }
        $transformer->addData($filter);
    }

    protected function transformPostMetaFilterData($filter, $options, &$transformer)
    {
        if (!isset($filter['meta_key'])) {
            return;
        }
        $filter['id'] = $filter['meta_key'];
        $options = empty($options) ? array() : $filter['options'];
        $filter['options'] = array();
        foreach ($options as $value => $label) {
            $filter['options'][] = apply_filters('jankx/filter/data/post_meta', array(
                'label' => $label,
                'id' => $value,
            ));
        }

        $transformer->addData($filter);
    }

    protected function createFiltersViaArrayTransfomer()
    {
        $transformer = new ArraysToFilterDataTransformer();
        $settings = $this->get_settings_for_display();

        $activeFilters = array_get($settings, 'active_filters', ['all']);
        $activeFilters = in_array('all', $activeFilters)
            ? array_keys($this->getActiveFilters(false))
            : $activeFilters;

        foreach ($activeFilters as $activeFilter) {
            if (!isset($this->filters[$activeFilter])) {
                continue;
            }
            $filter = $this->filters[$activeFilter];
            $options = array_get($settings, $activeFilter . '_filter', ['all']);
            if (empty($options)) {
                continue;
            }

            switch (array_get($filter, 'type')) {
                case 'taxonomy':
                    $this->transformTaxonomyFilterData($filter, $options, $transformer);
                    break;
                case 'post_meta':
                    $this->transformPostMetaFilterData($filter, $options, $transformer);
            }
        }

        return $transformer->getFilterDatas();
    }

    protected function render()
    {
        if (!class_exists(PostTypeFiltersRenderer::class)) {
            if (current_user_can('manage_theme')) {
                echo __('The theme require package "jankx/global-filter" to create filter widget', 'jankx_elementor');
            }
            return;
        }
        if (empty($this->filters)) {
            echo __('Please define support filters for Elementor widget', 'jankx_elementor');
            return;
        }

        $settings = $this->get_settings_for_display();
        $filters = new PostTypeFiltersRenderer();
        $filters->setOptions(array(
            'layout' => array_get($settings, 'layout', 'card'),
            'posts_per_page' => array_get($settings, 'limit', 10),
            'instance_id' => $this->instanceId,
            'filter_style' => array_get($settings, 'filter_style', 'simple'),
            'filters' => $this->createFiltersViaArrayTransfomer(),
        ));
        $filters->setLayoutOptions(array(
            'columns' => array_get($settings, 'columns', 4),
        ));
        echo $filters->render();
    }
}
