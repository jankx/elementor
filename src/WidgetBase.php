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

    protected function get_post_types_support_post_formats()
    {
        $post_types = get_post_types(array(
            'public' => true,
        ));

        return array_filter($post_types, function ($post_type) {
            return !empty(post_type_supports($post_type, 'post-formats'));
        });
    }

    protected function make_human_readable_post_format($post_format)
    {
        return preg_replace_callback('/(^\w|\_(\w))/', function ($matches) {
            if (isset($matches[2])) {
                return ' ' . strtoupper($matches[2]);
            }
            return strtoupper($matches[1]);
        }, $post_format);
    }

    public function get_human_readable_post_formats()
    {
        $post_formats = get_theme_support('post-formats');
        if (empty($post_formats)) {
            return array();
        }
        $post_formats = array_get($post_formats, 0);
        $ret = array(
            'standard' => __('Standard'),
        );
        foreach ($post_formats as $post_format) {
            $ret[$post_format] = $this->make_human_readable_post_format($post_format);
        }

        return $ret;
    }

    protected function registerExtraControls()
    {
    }

    public function _register_controls()
    {
        return call_user_func_array(
            array($this, 'register_controls'),
            func_get_args()
        );
    }
}
