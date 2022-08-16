<?php
namespace Jankx\Elementor\Compatibles;

use Jankx\Elementor\Compatibles\Jankx\FullPageLayout;
use Jankx\Elementor\Compatibles\Jankx\Preload;

class Bootstrap
{
    public function __construct()
    {
    }

    public function makeCompatibles()
    {
        FullPageLayout::getInstance();
        Preload::getInstance();
    }
}
