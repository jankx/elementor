<?php
namespace Jankx\Elementor\Widgets;

use Jankx\Elementor\BaseWidget;

class PostsTabs extends BaseWidget
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
