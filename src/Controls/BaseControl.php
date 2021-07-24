<?php
namespace AdvancedElementor\Controls;

use Elementor\Base_Control;

abstract class BaseControl extends Base_Control
{
    protected static $assetDirUrl;

    protected function get_asset_dir_url()
    {
        $assetDir = dirname(dirname(__DIR__)) . '/assets';
        $abspath = constant('ABSPATH');
        if (PHP_OS === 'WINNT') {
            $abspath = str_replace('\\', '/', $abspath);
            $assetDir = str_replace('\\', '/', $assetDir);
        }
        return str_replace($abspath, site_url('/'), $assetDir);
    }

    protected function get_asset_url($path = '')
    {
        if (is_null(static::$assetDirUrl)) {
            static::$assetDirUrl = $this->get_asset_dir_url();
        }
        return sprintf('%s/%s', static::$assetDirUrl, $path);
    }

    protected function get_control_uid($input_type = 'default')
    {
        return 'elementor-control-' . $input_type . '-{{{ data._cid }}}';
    }
}
