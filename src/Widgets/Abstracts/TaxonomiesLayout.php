<?php

namespace Jankx\Elementor\Widgets\Abstracts;

if (!defined('ABSPATH')) {
    exit('Cheating huh?');
}

use Jankx;
use WP_Term_Query;
use Elementor\Controls_Manager;
use Jankx\Elementor\WidgetBase;
use AdvancedElementor\Controls\Choices;
use Jankx\Widget\Renderers\TaxonomiesLayoutRenderer;
use Jankx\PostLayout\PostLayoutManager;
use Jankx\PostLayout\TermLayout\Card;
use Jankx\PostLayout\TermLayout\Carousel;

abstract class TaxonomiesLayout extends WidgetBase
{
    protected $taxonomies;

    public function get_name()
    {
        return 'taxonomies_layout';
    }

    public function get_title()
    {
        return __('Taxonomies Layout', 'jankx');
    }

    public function get_icon()
    {
        return 'eicon-archive';
    }

    public function get_categories()
    {
        return array(
            'theme-elements',
            Jankx::templateStylesheet()
        );
    }

    protected function get_taxomies_options()
    {
        $args = array(
            'public'   => true,
            '_builtin' => false
        );
        $taxonomies = get_taxonomies($args, 'objects');
        $options = array();
        foreach ($taxonomies as $taxonomy) {
            $options[$taxonomy->name] = $taxonomy->label;
        }

        return $options;
    }

    protected function get_terms()
    {
        $args = array(
            'hide_empty' => false
        );
        if (empty($this->taxonomies)) {
            $args['taxonomy'] = array_keys($this->get_taxomies_options());
        } else {
            $args['taxonomy'] = $this->taxonomies;
        }

        $terms = new WP_Term_Query($args);
        $ret = array();
        foreach ($terms->get_terms() as $term) {
            $ret[sprintf('%s_%d', $term->taxonomy, $term->term_id)] = sprintf('%s (%s)', $term->name, $term->taxonomy);
        }
        return $ret;
    }


    protected function register_controls()
    {
        $taxonmies_options = $this->get_taxomies_options();
        $termLayouts = PostLayoutManager::getLayouts(array(
            'data' => 'term',
            'field' => 'names',
        ));


        $this->start_controls_section('content_section', array(
            'label' => __('Content', 'jankx'),
            'tab' => Controls_Manager::TAB_CONTENT,
        ));

        $this->add_control(
            'taxonomy_terms',
            array(
                'label' => __('Terms', 'jankx'),
                'type' => Choices::CONTROL_NAME,
                'options' => $this->get_terms(),
                'default' => array(),
                'multiple' => true,
            )
        );

        $this->add_responsive_control(
            'layout',
            [
                'label' => __('Layout', 'jankx'),
                'type' => Controls_Manager::SELECT,
                'options' => $termLayouts,
                'default' => Card::LAYOUT_NAME
            ]
        );

         $this->add_control(
             'show_description',
             [
                'label' => __('Show description', 'jankx'),
                'type' => Controls_Manager::SWITCHER,
                'label_on' => __('Show', 'jankx'),
                'label_off' => __('Hide', 'jankx'),
                'return_value' => 'yes',
                'default' => 'no',
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
                'of_type' => 'layout',
                'condition' => array(
                    'layout' => array(Card::LAYOUT_NAME)
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
                'of_type' => 'layout',
                'condition' => array(
                )
            ]
        );

        $this->add_responsive_control(
            'limit',
            [

                'label' => __('Limit', 'jankx'),
                'type' => Controls_Manager::NUMBER,
                'min' => 0,
                'max' => 100,
                'step' => 1,
                'default' => 5,
            ]
        );

        $this->end_controls_section();
    }

    public function render()
    {
        $settings = $this->get_settings_for_display();

        $taxonomyRenderer = TaxonomiesLayoutRenderer::prepare(array(
            'taxonomy_terms' => array_get($settings, 'taxonomy_terms'),
            'layout' => $this->get_responsive_setting('layout', Card::LAYOUT_NAME),
            'show_description' => array_get($settings, 'show_description'),
            'columns' => $this->get_responsive_setting('columns', 4),
            'rows' => $this->get_responsive_setting('rows', 1),
            'limit' => $this->get_responsive_setting('limit', 1),
            'taxonomies' => $this->taxonomies,
        ));

        echo $taxonomyRenderer->render();
    }
}
