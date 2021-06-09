<?php
namespace Jankx\Elementor;

use ReflectionClass;
use Elementor\Controls_Manager;
use Elementor\Plugin;
use Elementor\Preview;

use Jankx;
use Jankx\Elementor\Widgets\Posts;
use Jankx\Elementor\Widgets\PostsTabs;

class Elementor
{
    const POST_META_TAB = 'post_meta_tab';
    const VERSION = '1.0.0';

    protected static $assetDirUrl;
    protected static $elementorCategory;

    public function integrate()
    {
        Controls_Manager::add_tab(
            'post_meta',
            __('Post Meta', 'jankx')
        );

        $layout = new Layout();
        add_action('template_redirect', array($layout, 'customTemplates'));

        add_action('elementor/elements/categories_registered', array($this, 'registerJankxCategory'));
        add_action('elementor/controls/controls_registered', array($this, 'registerJankxControls'));
        add_action('elementor/widgets/widgets_registered', array($this, 'registerJankxWidgets'));

        if (apply_filters('jankx_ecommerce_elementor_active_woocommerce_tab', true)) {
            add_action('elementor/elements/categories_registered', array($this, 'customWidgetCategories'));
        }
        if (apply_filters('jankx_plugin_elementor_silent_mode', false)) {
            add_filter('elementor/editor/localize_settings', array( $this, 'removeElementPromtionWidgets'));
        }

        add_action('wp_enqueue_scripts', array($this, 'registerScripts'));
    }

    public function registerJankxCategory($elementsManager)
    {
        $theme = Jankx::theme();
        if ($theme->parent()) {
            $theme = $theme->parent();
        }
        static::$elementorCategory = $theme->stylesheet;
        $elementsManager->add_category(
            static::$elementorCategory,
            array(
                'title' => $theme->get('Name'),
                'icon' => 'fa fa-feather',
            )
        );
    }

    public function registerJankxControls($controlsManager)
    {
    }

    public function registerJankxWidgets($widgetsManager)
    {
        $widgetsManager->register_widget_type(new Posts());
        $widgetsManager->register_widget_type(new PostsTabs());
    }

    public function removeElementPromtionWidgets($config)
    {
        // Remove Elementor promotion widgets to look good
        if (isset($config['promotionWidgets'])) {
            unset($config['promotionWidgets']);
        }

        return $config;
    }

    public function customWidgetCategories($elementManager)
    {
        $reflectElementManager = new ReflectionClass($elementManager);
        $widgetCategoryRefProp = $reflectElementManager->getProperty('categories');
        $widgetCategoryRefProp->setAccessible(true);

        $widgetCategories       = $widgetCategoryRefProp->getValue($elementManager);
        $highPriorityCategories = array_slice($widgetCategories, 0, 1);

        if (isset($widgetCategories[static::$elementorCategory])) {
            $highPriorityCategories[static::$elementorCategory] = $widgetCategories[static::$elementorCategory];
            unset($widgetCategories[static::$elementorCategory]);
        }
        if (isset($widgetCategories['woocommerce-elements'])) {
            $highPriorityCategories['woocommerce-elements'] = $widgetCategories['woocommerce-elements'];
            unset($widgetCategories['woocommerce-elements']);
            if (apply_filters('jankx_integrate_elementor_active_woocommerce', true)) {
                unset($highPriorityCategories['woocommerce-elements']['active']);
            }
        }

        $widgetCategories = array_merge($highPriorityCategories, $widgetCategories);
        $widgetCategoryRefProp->setValue($elementManager, $widgetCategories);
    }

    public static function asset_url($src)
    {
        if (is_null(static::$assetDirUrl)) {
            $assetDir = sprintf('%s/assets', JANKX_ELEMENTOR_ROOT_DIR);
            $abspath = constant('ABSPATH');
            if (PHP_OS === 'WINNT') {
                $abspath = str_replace('\\', '/', $abspath);
                $assetDir = str_replace('\\', '/', $assetDir);
            }
            static::$assetDirUrl = str_replace($abspath, site_url('/'), $assetDir);
        }

        return sprintf('%s/%s', static::$assetDirUrl, $src);
    }

    public function registerScripts()
    {
        $elementorPreview = Plugin::$instance->preview;

        if (is_a($elementorPreview, Preview::class) && $elementorPreview->is_preview_mode()) {
            wp_register_style(
                'elementor-edit-mode',
                static::asset_url('css/elementor-edit-mode.css'),
                array(),
                static::VERSION
            );

            wp_enqueue_style('elementor-edit-mode');
        }
    }
}
