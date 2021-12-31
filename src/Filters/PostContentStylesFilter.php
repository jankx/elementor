<?php
namespace Jankx\Elementor\Filters;

use Jankx\Filter;
use Elementor\Widget_Text_Editor;

class PostContentStylesFilter extends Filter {
    protected $hooks = 'elementor/widget/render_content';
    protected $argsCounter = 2;

    public function execute($content, $widgetBase) {
        if (is_a($widgetBase, Widget_Text_Editor::class)) {
            return str_replace('class="elementor-text-editor ', 'class="elementor-text-editor entry-content ', $content);
        }

        return $content;
    }
}
