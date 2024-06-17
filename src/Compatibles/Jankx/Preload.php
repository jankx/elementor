<?php

namespace Jankx\Elementor\Compatibles\Jankx;

use Elementor\Controls_Manager;
use Jankx;

class Preload
{
    protected static $instance;
    protected static $added = false;

    protected function __construct()
    {
        $this->initHooks();
    }

    public static function getInstance()
    {
        if (is_null(static::$instance)) {
            static::$instance = new static();
        }
        return static::$instance;
    }

    public function initHooks()
    {
        add_action('elementor/element/after_section_end', [
            $this,
            'addPreloadFields'
        ], 10, 2);
    }

    /**
     * @param \Elementor\Controls_Stack $controlStacks
     */
    public function addPreloadFields($controlStacks, $sectionId)
    {
        if ($sectionId !== 'section_advanced' || static::$added) {
            return;
        }
        $controlStacks->start_controls_section(
            'jankx_preload',
            [
                'label' => esc_html__(sprintf('%s Preload', Jankx::templateName()), 'jankx'),
                'tab' => Controls_Manager::TAB_ADVANCED,
            ]
        );

        $controlStacks->add_control(
            'fullpage_enabled',
            [
                'label' => esc_html__('Preload Background', 'jankx'),
                'type' => Controls_Manager::SWITCHER,
                'default' => 'no',
            ]
        );

        $controlStacks->end_controls_section();
        static::$added = true;
    }
}
