<?php

namespace Jankx\Elementor;

if (!defined('ABSPATH')) {
    exit('Cheatin huh?');
}

use ReflectionClass;
use Elementor\Controls_Manager;
use Elementor\Plugin;
use Elementor\Preview;
use AdvancedElementor\Controls\Choices;
use Jankx;
use Jankx\Blocks\PostType as BlockPostType;
use Jankx\Elementor\Compatibles\Bootstrap;
use Jankx\Elementor\Filters\PostContentStylesFilter;
use Jankx\Elementor\Widgets\Posts;
use Jankx\Elementor\Widgets\PostsTabs;
use Jankx\Elementor\Widgets\Blocks;
use Jankx\Elementor\Widgets\GradientHeading;
use Jankx\Elementor\Widgets\PageSelector;
use Jankx\Elementor\Widgets\SocialSharing;
use Jankx\Elementor\Widgets\LinkTabs;

class Elementor
{
    const POST_META_TAB = 'post_meta_tab';
    const VERSION = '1.0.0';

    protected static $assetDirUrl;
    protected static $elementorCategory;

    protected static $elementorVersion;
    protected static $registerWidgetMethod;

    public function integrate()
    {
        static::$elementorCategory = Jankx::templateStylesheet();
        static::$elementorVersion  = Elementor::VERSION;
        Controls_Manager::add_tab(
            'post_meta',
            __('Post Meta', 'jankx')
        );

        $layout = new Layout();
        add_action('template_redirect', array($layout, 'customTemplates'));

        $icons = new Icons();
        $icons->loadIcons();

        add_action('elementor/elements/categories_registered', array($this, 'registerJankxCategory'));
        add_action('elementor/controls/controls_registered', array($this, 'registerJankxControls'));
        add_action('elementor/widgets/register', array($this, 'registerJankxWidgets'));

        if (apply_filters('jankx_woocommerce_elementor_active_woocommerce_tab', true)) {
            add_action('elementor/elements/categories_registered', array($this, 'customWidgetCategories'));
        }
        if (apply_filters('jankx_plugin_elementor_silent_mode', false)) {
            add_filter('elementor/editor/localize_settings', array( $this, 'removeElementPromtionWidgets'));
        }

        add_action('wp_enqueue_scripts', array($this, 'registerScripts'));

        Jankx::addFilter(PostContentStylesFilter::class);

        // Make compatibles with other features
        $compatibles = new Bootstrap();
        $compatibles->makeCompatibles();
    }

    public function registerJankxCategory($elementsManager)
    {
        $elementsManager->add_category(
            static::$elementorCategory,
            array(
                'title' => Jankx::templateName(),
                'icon' => 'fa fa-feather',
            )
        );
    }

    public function registerJankxControls($controls_manager)
    {
        $controls_manager->register(new Choices());
    }

    /**
     * @param \Elementor\Widgets_Manager $widgetsManager
     * @param \Elementor\Widget_Base $widget
     *
     * @return boolean
     */
    public function registerWidget($widgetsManager, $widget)
    {
        if (is_null(static::$registerWidgetMethod)) {
            static::$registerWidgetMethod = version_compare(static::$elementorVersion, '3.5.0')
                ? 'register'
                : 'register_widget_type';
        }
        return call_user_func([$widgetsManager, static::$registerWidgetMethod], $widget);
    }

    /**
     * @param \Elementor\Widgets_Manager $widgetsManager
     */
    public function registerJankxWidgets($widgetsManager)
    {
        $this->registerWidget($widgetsManager, new Posts());
        $this->registerWidget($widgetsManager, new PostsTabs());
        $this->registerWidget($widgetsManager, new PageSelector());
        $this->registerWidget($widgetsManager, new SocialSharing());
        $this->registerWidget($widgetsManager, new LinkTabs());
        $this->registerWidget($widgetsManager, new GradientHeading());

        if (class_exists(BlockPostType::class)) {
            $this->registerWidget($widgetsManager, new Blocks());
        }
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

    public function loadTemplateInEditingMode($load)
    {
        if (isset($_GET['action']) && $_GET['action'] === 'elementor') {
            return true;
        }
        return $load;
    }
}
