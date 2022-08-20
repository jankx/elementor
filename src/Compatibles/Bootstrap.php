<?php
namespace Jankx\Elementor\Compatibles;

use Jankx\Elementor\Compatibles\Jankx\FullPageLayout;
use Jankx\Elementor\Compatibles\Jankx\Preload;
use Jankx\Elementor\Compatibles\Jankx\SiteLayoutCompatibles;

class Bootstrap
{
    public function __construct()
    {
    }

    public function makeCompatibles()
    {
        FullPageLayout::getInstance();
        Preload::getInstance();
        SiteLayoutCompatibles::getInstance();
    }
}
