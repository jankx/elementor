<?php

namespace Jankx\Elementor;

use Jankx\IconFonts;
use Jankx\IconFonts\GeneratorManager;

class Icons
{
    public function loadIcons()
    {
        add_action('jankx/icon/fonts/new', array($this, 'loadJankxIcons'), 10, 5);
        add_action('wp_ajax_jankx-elementor-fetch-icons', array($this, 'fetchJsonIcons'));
    }

    public function fetchJsonIcons()
    {
        $iconFonts = IconFonts::getInstance();
        $all_fonts = $iconFonts->getFonts();
        $current_font = array_get($_GET, 'font', '');
        $font_handle = sprintf('%s-font', $current_font);
        $icons = array();
        if (isset($all_fonts[$font_handle])) {
            $font = $all_fonts[$font_handle];
            $generator = GeneratorManager::detectGenerator(
                $current_font,
                array_get($font, 'path'),
                array_get($font, 'font-family')
            );

            if ($generator) {
                $icons = array_map(function ($item) use ($generator) {
                    return str_replace($generator->getDisplayPrefix(), '', $item);
                }, $generator->getGlyphMaps());
            }
        }

        echo json_encode(array(
            'icons' => array_values($icons),
        ));
        exit();
    }

    public function loadJankxIcons($font_name, $font_css_path, $display_name, $font_family, $ver)
    {
        /**
         * @var \Jankx\IconFonts\FontIconGenerator
         */
        $fontGenerator = GeneratorManager::detectGenerator($font_name, $font_css_path, $font_family, $ver);
        add_filter(
            'elementor/icons_manager/native',
            function ($icons) use ($font_name, $font_css_path, $display_name, $fontGenerator) {
                $icons[$font_name] = array(
                    'name' => $fontGenerator->getFontName(),
                    'label' => $display_name,
                    'url' => jankx_get_path_url($font_css_path),
                    'enqueue' => [],
                    'prefix' => $fontGenerator->detectPrefix(),
                    'displayPrefix' => '',
                    'labelIcon' => $fontGenerator->getFontFamily(),
                    'ver' => $fontGenerator->getVersion(),
                    'fetchJson' => admin_url(sprintf('admin-ajax.php?action=jankx-elementor-fetch-icons&font=%s', $font_name)),
                    'native' => true,
                );

                return $icons;
            }
        );
    }
}
