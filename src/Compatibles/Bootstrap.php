<?php
namespace Jankx\Elementor\Compatibles;

use Jankx\Elementor\Compatibles\Jankx\FullPageLayout;
use Jankx\Elementor\Compatibles\Jankx\Preload;
use Jankx\Elementor\Compatibles\Jankx\SiteLayoutCompatibles;
use Jankx\FullPage\Loader;

class Bootstrap
{
    public function __construct()
    {
    }

    public function makeCompatibles()
    {
        if (class_exists(Loader::class)) {
            FullPageLayout::getInstance();
        }

        Preload::getInstance();
        SiteLayoutCompatibles::getInstance();
    }
}
