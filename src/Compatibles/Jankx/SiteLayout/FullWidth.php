<?php

namespace Jankx\Elementor\Compatibles\Jankx\SiteLayout;

use Elementor\Plugin;
use Jankx\Elementor\Compatibles\Jankx\Abstracts\SiteLayoutCompatible;

class FullWidth extends SiteLayoutCompatible
{
    public function mock()
    {
        add_action('jankx/template/page/render/before', function () {
            Plugin::$instance->frontend->add_body_class('elementor-template-full-width');
        });

        add_filter('jankx/template/site/content/pre', function () {
            // Elementor echo content
            return Plugin::$instance->modules_manager->get_modules('page-templates')->print_content();
        });

        do_action('jankx/template/page/content/before', function () {
            do_action('elementor/page_templates/header-footer/before_content');
        });

        do_action('jankx/template/page/content/after', function () {
            do_action('elementor/page_templates/header-footer/after_content');
        });
    }
}
