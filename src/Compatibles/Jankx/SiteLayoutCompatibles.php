<?php
namespace Jankx\Elementor\Compatibles\Jankx;

use Jankx\Elementor\Compatibles\Jankx\SiteLayout\FullWidth;

class SiteLayoutCompatibles
{
    protected static $instance;

    protected static $layoutCompatibleInstances = [];

    protected function __construct()
    {
        $this->initHooks();
    }

    public static function getInstance()
    {
        if (is_null(static::$instance)) {
            static::$instance = new static();
        }
        return static::$instance;
    }

    public function init()
    {
        static::$layoutCompatibleInstances = array_merge(apply_filters('jankx/elementor/compatible/layouts', []), [
            'wp-content/plugins/elementor/modules/page-templates/templates/header-footer.php' => FullWidth::class,
        ]);
    }

    protected function initHooks()
    {
        add_action('init', [$this, 'init']);
        add_action('jankx/template/renderer/pre', [$this, 'compatibles'], 10, 2);
    }

    public function compatibles($page, $templateFile)
    {
        $templateFileRelativePath = str_replace(ABSPATH, '', $templateFile);
        if (isset(static::$layoutCompatibleInstances[$templateFileRelativePath]) && class_exists(($cls = static::$layoutCompatibleInstances[$templateFileRelativePath]))) {
            add_filter('alway_use_jankx_template_engine_system', '__return_true');
            /**
             * @var \Jankx\Elementor\Compatibles\Jankx\Abstracts\SiteLayoutCompatible
             */
            $obj = new $cls();
            $obj->mock();
        }
    }
}
