<?php
namespace Jankx\Elementor\Widgets;

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

        $this->add_control('post_layout', array(
            'label' => __('Layout', 'jankx'),
            'type' => Controls_Manager::SELECT,
            'options' => PostLayoutManager::getLayouts(array(
                'exclude' => 'parent',
                'field' => 'names',
            )),
            'default' => Card::LAYOUT_NAME,
        ));

        $this->add_control('columns', array(
            'label' => __('Columns', 'jankx'),
            'type' => Controls_Manager::NUMBER,
            'default' => 4,
        ));

        $this->add_control('limit', array(
            'label' => __('Limit', 'jankx'),
            'type' => Controls_Manager::NUMBER,
            'default' => 10,
        ));

        $this->end_controls_section();
    }

    protected function render()
    {
        $settings = $this->get_settings_for_display();

        $renderer = PostTypePostsRenderer::prepare(array(
            'post_type' => $this->post_type,
            'layout' => array_get($settings, 'post_layout', Card::LAYOUT_NAME),
            'posts_per_page' => array_get($settings, 'limit', 10),
            'featured_meta_key' => $this->featured_meta_key,
            'featured_meta_value' => $this->featured_meta_value,
        ), static::class);

        $renderer->setLayoutOptions(array(
            'columns' => array_get($settings, 'columns', 4),
        ));

        echo $renderer->render();
    }
}
