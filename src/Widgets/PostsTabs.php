<?php
namespace Jankx\Elementor\Widgets;

use Jankx;
use Jankx\Elementor\Widgets\Abstracts\PostTypeTabs;

class PostsTabs extends PostTypeTabs
{
    public function get_name()
    {
        return 'posts_tabs';
    }

    public function get_title()
    {
        return sprintf(
            '%s %s',
            Jankx::templateName(),
            __('Posts Tabs', 'jankx')
        );
    }

    protected function createTabs()
    {
        $this->filters = array(
            'post_category' => array(
                'type' => 'taxonomy',
                'taxonomy' => 'category',
                'label' => __('Category'),
            ),
            'post_tag' => array(
                'type' => 'taxonomy',
                'taxonomy' => 'post_tag',
                'label' => __('Post Tags', 'jankx'),
            ),
        );
    }
}
