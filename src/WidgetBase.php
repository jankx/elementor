<?php
namespace Jankx\Elementor;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Jankx\PostLayout\PostLayoutManager;

abstract class WidgetBase extends Widget_Base
{
    protected function getImageSizeName($sizeName)
    {
        switch ($sizeName) {
            case 'thumbnail':
                return __('Thumbnail');
            case 'medium':
                return __('Medium');
            case 'large':
                return __('Large');
            default:
                return preg_replace_callback(array(
                    '/^(\w)/',
                    '/(\w)([\-|_]{1,})/'
                ), function ($matches) {
                    if (isset($matches[2])) {
                        return sprintf('%s ', $matches[1]) ;
                    } elseif (isset($matches[1])) {
                        return strtoupper($matches[1]);
                    }
                }, $sizeName);
        }
    }

    protected function getImageSizes()
    {
        $ret = array();
        foreach (get_intermediate_image_sizes() as $imageSize) {
            if (apply_filters('jankx_image_size_ignore_medium_large_size', true)) {
                if ($imageSize === 'medium_large') {
                    continue;
                }
            }
            $ret[$imageSize] = $this->getImageSizeName($imageSize);
        }
        $ret['full'] = __('Full size', 'jankx');
        $ret['custom'] = __('Custom Size', 'jankx');

        return $ret;
    }

    public function addThumbnailControls()
    {
        $this->add_control(
            'show_post_thumbnail',
            [
                'label' => __('Show Thumbnail', 'jankx'),
                'type' => Controls_Manager::SWITCHER,
                'label_on' => __('Show', 'jankx'),
                'label_off' => __('Hide', 'jankx'),
                'return_value' => 'yes',
                'default' => 'yes',
            ]
        );

        $this->add_control(
            'thumbnail_size',
            [
                'label' => __('Image size', 'jankx'),
                'type' => Controls_Manager::SELECT,
                'options' => $this->getImageSizes(),
                'default' => 'medium',
            ]
        );

        $this->add_control(
            'image_width',
            [
                'label' => __('Image Width', 'jankx'),
                'type' => Controls_Manager::NUMBER,
                'min' => 0,
                'step' =>5,
                'default' => 400,
                'condition' => array(
                    'thumbnail_size' => 'custom'
                )
            ]
        );

        $this->add_control(
            'image_height',
            [
                'label' => __('Image Height', 'jankx'),
                'type' => Controls_Manager::NUMBER,
                'min' => 0,
                'step' =>5,
                'default' => 320,
                'condition' => array(
                    'thumbnail_size' => 'custom',
                )
            ]
        );
    }

    public function getImageSizeFromSettings($settings)
    {
        $imageSize = array_get($settings, 'thumbnail_size', 'thumbnail');
        if ($imageSize === 'custom') {
            $imageSize = array(
                array_get($settings, 'image_width', 150),
                array_get($settings, 'image_height', 150)
            );
        }
        return $imageSize;
    }

    protected function get_page_options($prefix = '')
    {
        $pages = get_pages(array());
        $options = array();
        foreach ($pages as $page) {
            $options[sprintf('%s%s', $prefix, $page->ID)] = $page->post_title;
        }

        return apply_filters('jankx_elementor_get_page_options', $options);
    }
}
