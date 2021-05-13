<?php
namespace Jankx\Elementor;

use ReflectionClass;
use Elementor\Controls_Manager;

use Jankx\Elementor\Widgets\Posts;
use Jankx\Elementor\Widgets\PostsTabs;

class Elementor
{
    const POST_META_TAB = 'post_meta_tab';

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
    }

    public function registerJankxCategory($elementsManager)
    {
        $elementsManager->add_category(
            'jankx',
            array(
                'title' => __('Jankx Elements', 'jankx'),
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

        if (isset($widgetCategories['jankx'])) {
            $highPriorityCategories['jankx'] = $widgetCategories['jankx'];
            unset($widgetCategories['jankx']);
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
}
