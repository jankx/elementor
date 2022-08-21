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

    protected static $isRegisterToWrapper = false;
    protected static $currentLayout;
    protected static $isHackEndWrapper = false;

    protected $lastId = '';

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
        ], 10, 3);

        add_action(
            'elementor/document/after_save',
            [$this, 'updateSiteLayout']
        );
        add_filter(
            'attribute_escape',
            [$this, 'registerFullPageToElementorWrapper'],
            10,
            2
        );

        add_action(
            'jankx/fullpage/objects',
            [$this, 'customizeJankFullPageObject']
        );

        add_action(
            'elementor/frontend/before_render',
            [$this, 'makeElementorSectionAsFullPageSlide']
        );
        add_action('elementor/frontend/after_render', [$this, 'commentOutCloseElementorWrapper']);
    }

    protected function currentSiteLayout()
    {
        if (is_null(static::$currentLayout)) {
            static::$currentLayout = SiteLayout::getInstance()->getLayout();
        }
        return static::$currentLayout;
    }

    /**
     * @param \Elementor\Core\Base\Document $controls_stack
     */
    public function registerFullLayoutSettings($document, $section_id, $args)
    {
        if (is_a($document, Document::class)) {
            $elements    = $document->get_elements_data();
            $lastElement = end($elements);
            if (isset($lastElement['id'])) {
                $this->lastId = $lastElement['id'];
            }
        }

        if ($section_id === 'document_settings') {
            $currentLayout = $this->currentSiteLayout();
            $document->add_control(
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

    public function registerFullPageToElementorWrapper($safe_text, $text)
    {
        if (static::$isRegisterToWrapper) {
            remove_filter(
                'attribute_escape',
                [$this, 'registerFullPageToElementorWrapper'],
                10
            );
            return;
        }

        if (preg_match('/^elementor elementor-\d{1,}( .+)?$/', $text)) {
            static::$isRegisterToWrapper = true;

            return $safe_text . ' jankx-fullpage-wrapper';
        }
        return $safe_text;
    }

    public function customizeJankFullPageObject($object)
    {
        return array_merge($object, [
            'sectionSelector' => '.elementor-top-section',
            'credits' => [
                'enabled' => false,
            ]
        ]);
    }

    /**
     * @param \Elementor\Element_Base $element
     */
    public function makeElementorSectionAsFullPageSlide($element)
    {
        if ($this->currentSiteLayout() !== 'jankx-fullpage' || $element->get_type() !== 'section') {
            return;
        }
        $element->add_render_attribute('_wrapper', 'class', 'fp-section fp-table');
    }

    /**
     * @param \Elementor\Element_Column $element
     */
    public function commentOutCloseElementorWrapper($element)
    {
        if ($element->get_id() === $this->lastId) {
            static::$isHackEndWrapper = true;
            echo '<!--';
        }
    }

    /**
     * @return boolean
     */
    public static function isHackEndWrapper()
    {
        return boolval(static::$isHackEndWrapper);
    }
}
