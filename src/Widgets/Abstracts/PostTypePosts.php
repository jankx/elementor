<?php
namespace Jankx\Elementor\Widgets\Abstracts;

use Elementor\Controls_Manager;
use Jankx;
use Jankx\Elementor\WidgetBase;
use Jankx\Widget\Renderers\PostTypePostsRenderer;
use Jankx\PostLayout\PostLayoutManager;
use Jankx\PostLayout\Layout\Card;

abstract class PostTypePosts extends WidgetBase
{
    protected $post_type = 'post';
    protected $featured_meta_key = '';
    protected $featured_meta_value;

    protected function get_data_type_options()
    {
        $data_types = array(
            'recents' => __('Recents')
        );
        if ($this->featured_meta_key) {
            $data_types['featured'] = __('Featured', 'jankx');
        }
        $data_types['specifics'] = __('Specifics', 'jankx');

        return $data_types;
    }

    public function get_categories()
    {
        return array(
            'theme-elements',
            Jankx::templateStylesheet()
        );
    }

    protected function register_controls()
    {
        $this->start_controls_section(
            'content',
            array(
                'label' => __('Content'),
                'tab' => Controls_Manager::TAB_CONTENT,
            )
        );

        $this->add_control('data_type', array(
            'label' => __('Data Type', 'jankx'),
            'type' => Controls_Manager::SELECT,
            'options' => $this->get_data_type_options(),
            'default' => 'recents',
        ));

        $this->add_responsive_control('post_layout', array(
            'label' => __('Layout', 'jankx'),
            'type' => Controls_Manager::SELECT,
            'options' => PostLayoutManager::getLayouts(array(
                'exclude' => 'parent',
                'field' => 'names',
            )),
            'default' => Card::LAYOUT_NAME,
        ));

        $this->addThumbnailControls();
        $this->registerExtraControls();

        $this->add_responsive_control('columns', array(
            'label' => __('Columns', 'jankx'),
            'type' => Controls_Manager::NUMBER,
            'default' => 4,
        ));

        $this->add_responsive_control('rows', array(
            'label' => __('Rows', 'jankx'),
            'type' => Controls_Manager::NUMBER,
            'default' => 1,
        ));

        $this->add_responsive_control('limit', array(
            'label' => __('Limit', 'jankx'),
            'type' => Controls_Manager::NUMBER,
            'default' => 10,
        ));

        $this->end_controls_section();
    }

    protected function getRendererOptions()
    {
        $settings = $this->get_settings_for_display();
        return array(
            'post_type' => $this->post_type,
            'layout' => $this->get_responsive_setting('post_layout', Card::LAYOUT_NAME),
            'posts_per_page'  => $this->get_responsive_setting('limit', 10),
            'featured_meta_key' => $this->featured_meta_key,
            'featured_meta_value' => $this->featured_meta_value,
        );
    }

    protected function getLayoutOptions()
    {
        $settings = $this->get_settings_for_display();
        return array(
            'show_thumbnail' => array_get($settings, 'show_thumbnail', true),
            'thumbnail_size' => array_get($settings, 'thumbnail_size', 'thumbnail'),
            'thumbnail_position' => 'top',
            'item_style' => array_get($settings, 'item_style'),
            'columns' => $this->get_responsive_setting('columns', 4),
            'rows' => $this->get_responsive_setting('rows', 4),
        );
    }

    protected function render()
    {
        $renderer = PostTypePostsRenderer::prepare(
            $this->getRendererOptions(),
            static::class
        );
        $renderer->setLayoutOptions($this->getLayoutOptions());

        echo $renderer->render();
    }
}
