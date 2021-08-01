<?php
namespace Jankx\Elementor\Widgets;

use Jankx;
use Jankx\Elementor\WidgetBase;

class PostsTabs extends WidgetBase
{
    protected $postType = 'post';

    public function get_name()
    {
        return 'jankx_posts_tabs';
    }

    public function get_title()
    {
        return __('Jankx Posts Tabs', 'jankx');
    }

    public function get_categories()
    {
        return array(
            'theme-elements',
            Jankx::templateStylesheet()
        );
    }

    protected function render()
    {
        echo 'jankx posts tabs';
    }
}
