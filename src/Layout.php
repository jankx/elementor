<?php

namespace Jankx\Elementor;

if (!defined('ABSPATH')) {
    exit('Cheating huh?');
}

use Elementor\Core\Base\Document;
use Elementor\Core\Settings\Manager;
use Elementor\Core\Responsive\Responsive;
use Jankx\Asset\CustomizableAsset;
use Jankx\Asset\Cache;

class Layout
{
    public function customTemplates()
    {
        add_action('template_redirect', array($this, 'integrateTemplateClasses'), 30);
    }

    public function integrateTemplateClasses()
    {
        $document = \Elementor\Plugin::instance()->documents->get(get_the_ID());
        if (is_singular() && $document instanceof Document && $document->is_built_with_elementor()) {
            $settings = array_get($document->get_data(), 'settings');
            $template = array_get($settings, 'template', 'default');

            add_filter('jankx/template/content_sidebar/container/disabled', function () use ($template) {
                return $template === 'elementor_header_footer';
            });

            if (apply_filters('jankx_template_enable_compatible_elementor_container', true)) {
                add_action('jankx/template/container/open/before', array($this, 'openElementorSelectionClass'));
                add_action('jankx/template/container/close/after', array($this, 'closeElementorSelectionClass'));

                add_filter('jankx/layout/based/common-css', '__return_false');
                add_filter('jankx/template/container/classes', array($this, 'addElementorContainerClass'));
            }
        }

        if (apply_filters('jankx_template_use_elementor_container_width', true)) {
            add_action('wp_enqueue_scripts', array($this, 'cloneContainerStylesheets'), 9);
        }
    }

    public function removeContentSidebarContainer()
    {
        remove_action('jankx_template_after_header', array($this, 'openJankxSidebarContentContainer'), 20);
        remove_action('jankx_template_before_footer', array($this, 'closeJankxSidebarContentContainer'), 4);
    }

    public function openElementorSelectionClass()
    {
        echo '<div class="elementor-section elementor-section-boxed jankx-elementor">';
    }

    public function closeElementorSelectionClass()
    {
        echo '</div><!-- End elementor-section by Jankx framework -->';
    }

    public function addElementorContainerClass($classes)
    {
        $classes[] = 'elementor-container';

        return $classes;
    }

    public function cloneContainerStylesheets()
    {
        if (Cache::globalCssIsExists()) {
            return;
        }
        $elementor_kit = get_option('elementor_active_kit');
        if (!$elementor_kit) {
            return;
        }

        $page = Manager::get_settings_managers('page')->get_model($elementor_kit);
        $page_settings = $page->get_data('settings');

        $container_width = array(
            'width' => 1140,
            'unit' => 'px'
        );
        if (isset($page_settings['container_width'])) {
            $settings = $page_settings['container_width'];
            $container_width = array(
                'width' => array_get($settings, 'size', 1410),
                'unit' => array_get($settings, 'unit', 'px'),
            );
        }

        $container_width_tablet = array(
            'width' => 1025,
            'unit' => 'px'
        );
        if (isset($page_settings['container_width_tablet'])) {
            $settings = $page_settings['container_width_tablet'];
            $container_width_tablet = array(
                'width' => array_get($settings, 'size', 1025),
                'unit' => array_get($settings, 'unit', 'px'),
            );
        }

        $container_width_mobile = array(
            'width' => 768,
            'unit' => 'px'
        );
        if (isset($page_settings['container_width_mobile'])) {
            $settings = $page_settings['container_width_mobile'];
            $container_width_mobile = array(
                'width' => array_get($settings, 'size', 768),
                'unit' => array_get($settings, 'unit', 'px'),
            );
        }

        $break_points = wp_parse_args(Responsive::get_breakpoints(), array(
            'xs' => 0,
            'sm' => 480,
            'md' => 768,
            'lg' => 1025,
            'xl' => 1440,
            'xxl' => 1600,
        ));

        $containerCSS = CustomizableAsset::loadCustomize(
            'elementor-wrapper.php',
            array(
                'desktop' => $container_width,
                'tablet' => $container_width_tablet,
                'mobile' => $container_width_mobile,
                'breakpoints' => $break_points,
            )
        );
        Cache::addGlobalCss($containerCSS);
    }
}
