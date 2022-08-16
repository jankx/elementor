<?php
namespace Jankx\Elementor\Compatibles\Jankx;

use Elementor\Controls_Manager;
use Elementor\Core\Base\Document;
use Jankx\FullPage\Common;
use Jankx\SiteLayout\Admin\Metabox\PostLayout;
use Jankx\SiteLayout\SiteLayout;

class FullPageLayout
{
    protected static $instance;

    /**
     * @var \Elementor\Core\Base\Document
     */
    protected $document;

    public static function getInstance()
    {
        if (is_null(static::$instance)) {
            static::$instance = new static();
        }
        return static::$instance;
    }

    protected function __construct()
    {
        $this->initHooks();
    }

    public function initHooks()
    {
        add_action('elementor/element/before_section_end', [
            $this,
            'registerFullLayoutSettings'
        ], 10, 2);

        add_action(
            'elementor/document/after_save',
            [$this, 'updateSiteLayout']
        );
    }

    /**
     * @param \Elementor\Controls_Stack $controls_stack
     */
    public function registerFullLayoutSettings($controls_stack, $section_id)
    {
        if ($section_id === 'document_settings') {
            $currentLayout = SiteLayout::getInstance()->getLayout();
            $controls_stack->add_control(
                'fullpage_enabled',
                [
                    'label' => esc_html__('Enable FullPage', 'jankx'),
                    'type' => Controls_Manager::SWITCHER,
                    'default' => $currentLayout === Common::LAYOUT_FULL_PAGE ? 'yes' : 'no',
                ]
            );
        }
    }

    /**
     * @return boolean
     */
    protected function checkFullpageEnabled($fullpage_enabled)
    {
        return $fullpage_enabled === 'yes';
    }

    /**
     * @param \Elementor\Core\DocumentTypes\Page $data
     */
    public function updateSiteLayout($data)
    {
        if (!$this->checkFullpageEnabled($data->get_settings('fullpage_enabled'))) {
            update_post_meta($data->get_main_id(), PostLayout::POST_LAYOUT_META_KEY, Common::LAYOUT_FULL_PAGE);
        } else {
            update_post_meta($data->get_main_id(), PostLayout::POST_LAYOUT_META_KEY, SiteLayout::getInstance()->getDefaultLayout());
        }
    }
}
