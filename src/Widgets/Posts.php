<?php
namespace Jankx\Elementor\Widgets;

use Jankx;
use Jankx\Specs\WP_Query;

use Elementor\Controls_Manager;
use Jankx\Widget\Renderers\PostsRenderer;
use Jankx\Elementor\WidgetBase;

use Jankx\PostLayout\PostLayoutManager;
use Jankx\PostLayout\Layout\ListLayout;
use Jankx\PostLayout\Layout\Card;
use Jankx\PostLayout\Layout\Carousel;
use Jankx\PostLayout\Layout\Preset3;
use Jankx\PostLayout\Layout\Preset5;

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
            __('%s Posts', 'jankx'),
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

    protected function get_orderby()
    {
        $orderBy = WP_Query::order_by();
        $orderBy['specific'] = __('Specifics', 'jankx_elementor');

        return $orderBy;
    }

    protected function register_controls()
    {
        $this->start_controls_section(
            'content_section',
            [
                'label' => __('Content', 'jankx'),
                'tab' => Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'title',
            [
                'label' => esc_html__('Title', 'elementor'),
                'type' => Controls_Manager::TEXTAREA,
                'dynamic' => [
                    'active' => true,
                ],
                'placeholder' => esc_html__('Enter your title', 'elementor'),
                'default' => esc_html__('Add Your Heading Text Here', 'elementor'),
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
            'post_format',
            [
                'label' => __('Post Format', 'jankx'),
                'type' => Controls_Manager::SELECT,
                'options' => $this->get_human_readable_post_formats(),
                'default' => 'standard',
                'condition' => array(
                    'post_type' => array_values($this->get_post_types_support_post_formats()),
                )
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
                'condition' => array(
                    'post_type' => array('post'),
                )
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
                'condition' => array(
                    'post_type' => array('post'),
                )
            ]
        );

        $this->add_responsive_control(
            'post_layout',
            [
                'label' => __('Layout', 'jankx'),
                'type' => Controls_Manager::SELECT,
                'default' => ListLayout::LAYOUT_NAME,
                'options' => PostLayoutManager::getLayouts(array(
                    'field' => 'names'
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


        $this->add_control(
            'show_carousel_pagination',
            [
                'label' => __('Carousel Pagination', 'jankx'),
                'type' => Controls_Manager::SWITCHER,
                'label_on' => __('Show', 'jankx'),
                'label_off' => __('Hide', 'jankx'),
                'return_value' => 'yes',
                'default' => 'no',
                'condition' => array(
                    'post_layout' => array(Carousel::LAYOUT_NAME, Preset5::LAYOUT_NAME)
                )
            ]
        );

        $this->addThumbnailControls();

        $this->add_control(
            'thumbnail_position',
            [
                'label' => __('Thumbnail position', 'jankx'),
                'type' => Controls_Manager::SELECT,
                'options' => $this->getImagePositions(),
                'default' => 'top',
                'condition' => array(
                    'show_post_thumbnail' => 'yes'
                )
            ]
        );


        // Define extra controls
        $this->registerExtraControls();

        $this->add_control(
            'orderby',
            [
                'label' => __('Order by', 'jankx'),
                'type' => Controls_Manager::SELECT,
                'default' => 'none',
                'options' => $this->get_orderby()
            ]
        );
        $this->add_control(
            'sort',
            [
                'label' => __('Sort', 'jankx'),
                'type' => Controls_Manager::SELECT,
                'default' => 'ASC',
                'options' => array(
                    'ASC' => __('Ascending'),
                    'DESC' => __('Descending')
                ),
                'condition' => array(
                    'orderby' => WP_Query::order_by(true),
                )
            ]
        );
        $this->add_control(
            'specific_data',
            [
                'label' => __('Specific Posts', 'jankx'),
                'type' => Controls_Manager::TEXT,
                'default' => '',
                'of_control' => 'orderby',
                'condition' => array(
                    'orderby' => 'specific',
                )
            ]
        );

        $this->add_responsive_control(
            'columns',
            [
                'label' => __('Columns', 'jankx'),
                'type' => Controls_Manager::NUMBER,
                'min' => 1,
                'max' => 10,
                'step' => 1,
                'default' => 4,
                'of_type' => 'post_layout'
            ]
        );

        $this->add_responsive_control(
            'rows',
            [
                'label' => __('Rows', 'jankx'),
                'type' => Controls_Manager::NUMBER,
                'min' => 1,
                'max' => 10,
                'step' => 1,
                'default' => 1,
            ]
        );

        $this->add_control(
            'last_columns_items',
            [
                'label' => __('Last Column Items', 'jankx'),
                'type' => Controls_Manager::NUMBER,
                'min' => 1,
                'max' => 10,
                'step' => 1,
                'default' => 3,
                'of_type' => 'post_layout',
                'condition' => array(
                    'post_layout' => array(Preset5::LAYOUT_NAME)
                )
            ]
        );

        $this->add_responsive_control(
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
                'default' => 20,
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

    protected function render()
    {
        $settings = $this->get_settings_for_display();
        $postsRenderer = PostsRenderer::prepare(array(
            'post_type' => array_get($settings, 'post_type', 'post'),
            'post_format'  => array_get($settings, 'post_format', 'standard'),
            'show_excerpt'  => array_get($settings, 'show_post_excerpt', 'no') === 'yes',
            'show_postdate'  => array_get($settings, 'show_postdate', 'no') == 'yes',
            'excerpt_length'  => array_get($settings, 'excerpt_length', 15),
            'categories'  => array_get($settings, 'post_categories', []),
            'tags'  => array_get($settings, 'post_tags', []),
            'show_title'  => array_get($settings, 'show_post_title', true),
            'show_pagination'  => array_get($settings, 'show_pagination', false),
            'thumbnail_position'  => array_get($settings, 'thumbnail_position', 'top'),
            'show_thumbnail'  => array_get($settings, 'show_post_thumbnail', true),
            'thumbnail_size'  => array_get($settings, 'thumbnail_size', 'thumbnail'),
            'last_columns_items'  => array_get($settings, 'last_columns_items', 3),
            'show_dot'  => array_get($settings, 'show_carousel_pagination', 'no') === 'yes',
            'orderby'  => array_get($settings, 'orderby', 'none'),
            'order'  => array_get($settings, 'sort', 'ASC'),
            'specific_data' => array_get($settings, 'specific_data', ''),
            'columns_mobile' => array_get($settings, 'columns_mobile'),
            'columns_tablet' => array_get($settings, 'columns_tablet'),
            'columns'  => $this->get_responsive_setting('columns', 4),
            'posts_per_page'  => $this->get_responsive_setting('posts_per_page', 10),
            'layout'  => $this->get_responsive_setting('post_layout', Card::LAYOUT_NAME),
            'rows'  => $this->get_responsive_setting('rows', 1),
        ));

        $widgetContent = $postsRenderer->render();

        if (($widgetTitle = array_get($settings, 'title')) && $widgetContent) {
            echo sprintf(
                '<h3 class="jankx-posts-title">
                   <span>%s</span>
                </h3>',
                $widgetTitle
            );
        }
        echo $widgetContent;
    }
}
