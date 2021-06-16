<?php
namespace Jankx\Elementor\Widgets;

use Jankx;
use Elementor\Controls_Manager;
use Jankx\PostLayout\PostLayoutManager;
use Jankx\Widget\Renderers\PostsRenderer;
use Jankx\Elementor\WidgetBase;

class Posts extends WidgetBase
{
    protected static $customFields = array();

    public function get_name()
    {
        return 'jankx_posts';
    }

    public function get_title()
    {
        return sprintf(
            __( '%s Posts', 'jankx' ),
            Jankx::templateName()
        );
    }

    public function get_icon()
    {
        return 'eicon-post-list';
    }

    public function get_categories()
    {
        return array(
            'theme-elements',
            Jankx::templateStylesheet()
        );
    }

    public function getPostCategories()
    {
        $taxQuery = array('taxonomy' => 'category', 'fields' => 'id=>name', 'hide_empty' => false);
        $postCats = version_compare($GLOBALS['wp_version'], '4.5.0') >= 0
            ? get_terms($taxQuery)
            : get_terms($taxQuery['taxonomy'], $taxQuery);

        return $postCats;
    }


    public function getPostTags()
    {
        $taxQuery = array('taxonomy' => 'post_tag', 'fields' => 'id=>name', 'hide_empty' => false);
        $postTags = version_compare($GLOBALS['wp_version'], '4.5.0') >= 0
            ? get_terms($taxQuery)
            : get_terms($taxQuery['taxonomy'], $taxQuery);

        return $postTags;
    }

    public function getImagePositions()
    {
        return array(
            'left' => __('Left'),
            'right' => __('Right'),
            'top' => __('Top'),
            'bottom' => __('Bottom'),
        );
    }

    public function getPostTypes()
    {
        $postTypes = array();

        $postTypeObjects = get_post_types(array(
            'public' => true,
        ), 'objects');
        foreach ($postTypeObjects as $postType => $object) {
            $postTypes[$postType] = $object->label;
        }
        return $postTypes;
    }

    protected function _register_controls()
    {
        $postLayout = PostLayoutManager::getInstance();
        $this->start_controls_section(
            'content_section',
            [
                'label' => __('Content', 'jankx'),
                'tab' => Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'widget_title',
            [
                'label' => __('Title', 'jankx'),
                'type' => Controls_Manager::TEXTAREA,
                'default' => __('Recent Posts', 'jankx'),
                'placeholder' => __('Input widget title', 'jankx'),
            ]
        );


        $this->add_control(
            'show_view_all_link',
            [
                'label' => __('View All URL', 'jankx'),
                'type' => Controls_Manager::URL,
                'default' => array(),
            ]
        );


        $this->add_control(
            'post_categories',
            [
                'label' => __('Post Categories', 'jankx'),
                'type' => Controls_Manager::SELECT2,
                'multiple' => true,
                'options' => $this->getPostCategories(),
                'default' => '',
            ]
        );

        $this->add_control(
            'post_tags',
            [
                'label' => __('Post Tags', 'jankx'),
                'type' => Controls_Manager::SELECT2,
                'multiple' => true,
                'options' => $this->getPostTags(),
                'default' => 'none',
            ]
        );

        $this->add_control(
            'post_type',
            [
                'label' => __('Post Type', 'jankx'),
                'type' => Controls_Manager::SELECT,
                'default' => 'post',
                'options' => $this->getPostTypes(),
            ]
        );

        $this->add_control(
            'post_layout',
            [
                'label' => __('Layout', 'jankx'),
                'type' => Controls_Manager::SELECT,
                'default' => PostLayoutManager::LIST_LAYOUT,
                'options' => $postLayout->getLayouts(array(
                    'type' => 'names'
                )),
            ]
        );

        $this->add_control(
            'show_post_title',
            [
                'label' => __('Show Post Title', 'jankx'),
                'type' => Controls_Manager::SWITCHER,
                'label_on' => __('Show', 'jankx'),
                'label_off' => __('Hide', 'jankx'),
                'return_value' => 'yes',
                'default' => 'yes',
            ]
        );

        $this->add_control(
            'show_pagination',
            [
                'label' => __('Show Pagination', 'jankx'),
                'type' => Controls_Manager::SWITCHER,
                'label_on' => __('Show', 'jankx'),
                'label_off' => __('Hide', 'jankx'),
                'return_value' => 'yes',
                'default' => 'no',
            ]
        );


        $this->addThumbnailControls();

        $this->add_control(
            'thumbnail_position',
            [
                'label' => __('Thumbnail position', 'jankx'),
                'type' => Controls_Manager::SELECT,
                'options' => $this->getImagePositions(),
                'default' => 'left',
                'condition' => array(
                    'show_post_thumbnail' => 'yes'
                )
            ]
        );

        $this->add_control(
            'show_post_excerpt',
            [
                'label' => __('Show Excerpt', 'jankx'),
                'type' => Controls_Manager::SWITCHER,
                'label_on' => __('Show', 'jankx'),
                'label_off' => __('Hide', 'jankx'),
                'return_value' => 'yes',
                'default' => 'no',
            ]
        );

        $this->add_control(
            'excerpt_length',
            [
                'label' => __('Excerpt length', 'jankx'),
                'type' => Controls_Manager::NUMBER,
                'min' => 0,
                'max' => 100,
                'step' => 1,
                'default' => 15,
                'condition' => array(
                    'show_post_excerpt' => 'yes'
                )
            ]
        );

        $this->add_control(
            'columns',
            [
                'label' => __('Columns', 'jankx'),
                'type' => Controls_Manager::NUMBER,
                'min' => 1,
                'max' => 10,
                'step' => 1,
                'default' => 4,
                'of_type' => 'post_layout',
                'condition' => array(
                    'post_layout' => array(PostLayoutManager::CARD, PostLayoutManager::CAROUSEL)
                )
            ]
        );

        $this->add_control(
            'rows',
            [
                'label' => __('Rows', 'jankx'),
                'type' => Controls_Manager::NUMBER,
                'min' => 1,
                'max' => 10,
                'step' => 1,
                'default' => 1,
                'of_type' => 'post_layout',
                'condition' => array(
                    'post_layout' => array(PostLayoutManager::CAROUSEL)
                )
            ]
        );

        $this->add_control(
            'posts_per_page',
            [

                'label' => __('Number of Posts', 'jankx'),
                'type' => Controls_Manager::NUMBER,
                'min' => 0,
                'max' => 100,
                'step' => 1,
                'default' => 5,
            ]
        );

        $this->end_controls_section();


    // Post metas section
        $this->start_controls_section(
            'post_meta_section',
            [
                'label' => __('Meta Appearance', 'plugin-name'),
                'tab' => 'post_meta',
            ]
        );

        $this->add_control(
            'show_postdate',
            [
                'label' => __('Show Post Date', 'jankx'),
                'type' => Controls_Manager::SWITCHER,
                'label_on' => __('Show', 'jankx'),
                'label_off' => __('Hide', 'jankx'),
                'return_value' => 'yes',
                'default' => 'no',
            ]
        );

        do_action('jankx_integration_elementor_posts_widget_metas', $this);

        $this->end_controls_section();
    }

    protected function render()
    {
        $settings = $this->get_settings_for_display();
        $rendererArgs = array();
        foreach ($this->mappingRenderFields() as $field => $rules) {
            if (empty($rules['map_to'])) {
                continue;
            }

            if (!isset($settings[$field])) {
                if (!isset($rules['default'])) {
                    continue;
                }
                $settings[$field] = $rules['default'];
            }
            $rendererArgs[$rules['map_to']] = isset($rules['value_type'])
                ? $this->parseValue($settings[$field], $rules['value_type'])
                : $settings[$field];
        }
        $postsRenderer = PostsRenderer::prepare($rendererArgs);

        echo $postsRenderer->render();
    }

    protected function _content_template()
    {
    }

    public function get_script_depends()
    {
        return array('splide');
    }

    public function get_style_depends()
    {
        return array('splide-theme');
    }

    protected function parseValue($value, $type)
    {
        if (in_array($type, array('boolean', 'bool'))) {
            return $value === 'yes'; // This is value of Elementor
        }
        return $value;
    }

    protected function mappingRenderFields()
    {
        $default = array(
            'show_post_excerpt' => array(
                'map_to' => 'show_excerpt',
                'value_type' => 'boolean'
            ),
            'show_postdate' => array(
                'map_to' => 'show_postdate',
                'value_type' => 'boolean'
            ),
            'excerpt_length' => array(
                'map_to' => 'excerpt_length',
            ),
            'post_categories' => array(
                'map_to' => 'categories',
            ),
            'post_tags' => array(
                'map_to' => 'tags',
            ),
            'widget_title' => array(
                'map_to' => 'header_text',
            ),
            'show_view_all_link' => array(
                'map_to' => 'view_all_url',
            ),
            'posts_per_page' => array(
                'map_to' => 'posts_per_page',
            ),
            'columns' => array(
                'map_to' => 'columns',
            ),
            'rows' => array(
                'map_to' => 'rows',
            ),
            'show_post_title' => array(
                'map_to' => 'show_title',
                'value_type' => 'boolean'
            ),
            'show_post_thumbnail' => array(
                'map_to' => 'show_thumbnail',
                'value_type' => 'boolean'
            ),
            'show_pagination' => array(
                'map_to' => 'show_pagination',
                'value_type' => 'boolean'
            ),
            'thumbnail_position' => array(
                'map_to' => 'thumbnail_position',
            ),
            'post_layout' => array(
                'map_to' => 'layout',
                'default' => PostLayoutManager::LIST_LAYOUT
            ),
            'post_type' => array(
                'map_to' => 'post_type',
                'default' => 'post',
            ),
            'thumbnail_size' => array(
                'map_to' => 'thumbnail_size',
                'default' => 'thumbnail'
            )
        );

        return array_merge(
            $default,
            static::$customFields
        );
    }

    public static function addCustomField($fieldName, $args = null)
    {
        if (empty($args)) {
            static::$customFields[$fieldName] = array(
                'map_to' => $fieldName
            );
        } else {
            static::$customFields[$fieldName] = $args;
        }
    }
}
