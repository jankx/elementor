<?php
namespace Jankx\Elementor;

use Jankx\Template\Page;
use Jankx\SiteLayout\SiteLayout;
use Elementor\Core\Settings\Manager;
use Elementor\Core\Responsive\Responsive;

class Layout
{
    public function customTemplates()
    {
        add_filter('jankx_template_pre_get_current_site_layout', array($this, 'makeFullwidthLayout'));
        add_action('template_redirect', array($this, 'integrateTemplateClasses'), 30);
    }

    public function makeFullwidthLayout($pre)
    {
        $page = Page::getInstance();
        $templateFile = $page->getTemplateFile();

        if (preg_match('/elementor\/.+\/templates\/header\-footer\.php$/', $templateFile)) {
            return SiteLayout::LAYOUT_FULL_WIDTH;
        }
        return $pre;
    }

    public function integrateTemplateClasses()
    {
        if (is_singular() && \Elementor\Plugin::instance()->db->is_built_with_elementor(get_the_ID())) {
            add_filter('jankx_template_disable_main_content_sidebar_container', '__return_true');

            if (apply_filters('jankx_template_enable_compatible_elementor_container', true)) {
                add_action('jankx_template_before_open_container', array($this, 'openElementorSelectionClass'));
                add_action('jankx_template_after_close_container', array($this, 'closeElementorSelectionClass'));

                add_filter('jankx_template_disable_base_css', '__return_true');
                add_filter('jankx_template_the_container_classes', array($this, 'addElementorContainerClass'));
            }
        }

        if (apply_filters('jankx_template_use_elementor_container_width', true)) {
            add_action('wp_head', array($this, 'cloneContainerStylesheets'), 9);
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
                'width'=>array_get($settings, 'size', 1410),
                'unit' =>array_get($settings, 'unit', 'px'),
            );
        }

        $container_width_tablet = array(
            'width' => 1025,
            'unit' => 'px'
        );
        if (isset($page_settings['container_width_tablet'])) {
            $settings = $page_settings['container_width_tablet'];
            $container_width_tablet = array(
                'width'=>array_get($settings, 'size', 1025),
                'unit' =>array_get($settings, 'unit', 'px'),
            );
        }

        $container_width_mobile = array(
            'width' => 768,
            'unit' => 'px'
        );
        if (isset($page_settings['container_width_mobile'])) {
            $settings = $page_settings['container_width_mobile'];
            $container_width_mobile = array(
                'width'=>array_get($settings, 'size', 768),
                'unit' =>array_get($settings, 'unit', 'px'),
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

        jankx_template('layout/elementor-wrapper', array(
            'desktop' => $container_width,
            'tablet' => $container_width_tablet,
            'mobile' => $container_width_mobile,
            'breakpoints' => $break_points,
        ));
    }
}
