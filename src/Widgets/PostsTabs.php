<?php
namespace Jankx\Elementor\Widgets;

use Jankx\Elementor\WidgetBase;

class PostsTabs extends WidgetBase
{
    public function get_name()
    {
        return 'jankx_posts_tabs';
    }

    public function get_title()
    {
        return __('Jankx Posts Tabs', 'jankx');
    }

    protected function render()
    {
        echo 'jankx posts tabs';
    }
}
