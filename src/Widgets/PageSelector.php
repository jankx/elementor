<?php
namespace Jankx\Elementor\Widgets;

use Jankx;
use Jankx\Elementor\WidgetBase;

class PageSelector extends WidgetBase
{
    public function get_name() {
        return 'page_selector';
    }

    public function get_title() {
        return sprintf(
            '%s %s',
            Jankx::templateName(),
            __('Page Selector', 'jankx')
        );
    }

    public function get_icon() {
        return 'eicon-checkbox';
    }

    public function render() {
        echo 'page selector';
    }
}
