<?php
namespace Jankx\Elementor\Traits;

trait FilterDataTrait
{
    protected function getTaxonomyFilterOptions($options)
    {
        $taxonomy = array_get($options, 'taxonomy');
        if (!$taxonomy) {
            return array();
        }
        $args = array(
            'taxonomy' => $taxonomy,
            'hide_empty' => false,
        );
        $terms = get_terms($args);
        $ret = array();

        foreach ($terms as $term) {
            $ret[sprintf('%s_%d', $term->taxonomy, $term->term_id)] = $term->name;
        }
        return $ret;
    }

    protected function getPostMetaFilterOptions($options)
    {
        $ret = array();
        if (isset($options['options'])) {
            $ret = $options['options'];
        }
        return $ret;
    }

    protected function getFilterOptions($args, $filterName)
    {
        $options = array(
            'all' => __('All'),
        );

        switch ($args['type']) {
            case 'taxonomy':
                return array_merge($options, $this->getTaxonomyFilterOptions($args));
            break;
            case 'post_meta':
                return array_merge($options, $this->getPostMetaFilterOptions($args));
            break;
        }

        return $options;
    }
}
