<?php

namespace Jankx\Elementor\Transformers;

use Jankx\Widget\Data\LinkTab;

class TransformSettingsToLinkTabs implements \Iterator, \Countable
{
    protected $currentIndex = 0;
    protected $widgetSettings = array();

    public function __construct($widgetSettings)
    {
        if (is_array($widgetSettings)) {
            $this->widgetSettings = $widgetSettings;
        }
    }

    public function key()
    {
        return $this->currentIndex;
    }

    public function valid()
    {
        return isset($this->widgetSettings[$this->currentIndex]);
    }

    protected function parseAttributes($attributesStr)
    {
        $attributes = array();
        if ($attributesStr) {
            $attributesArr = explode(',', $attributesStr);
            foreach ($attributesArr as $attributeItem) {
                $attr = explode('|', $attributeItem);
                if (!isset($attr[1])) {
                    $attr[1] = true;
                }

                list($name, $value) = $attr;

                $attributes[$name] = $value;
            }
        }
        return $attributes;
    }

    public function current()
    {
        $tab = $this->widgetSettings[$this->currentIndex];
        $tabTitle = array_get($tab, 'tab_title');
        $tabLink = array_get($tab, 'tab_link');

        $linkTab = new LinkTab(
            $tabTitle,
            array_get($tabLink, 'url'),
            array_get($tabLink, 'is_external'),
            array_get($tabLink, 'nofollow')
        );
        $attributes = $this->parseAttributes(array_get($tabLink, 'custom_attributes'));
        if (isset($attributes['active'])) {
            $linkTab->setActive($attributes['active'] != false);
            unset($attributes['active']);
        }
        $linkTab->setAttributes($attributes);

        return $linkTab;
    }

    public function next()
    {
        $this->currentIndex += 1;
    }

    public function rewind()
    {
        $this->currentIndex = 0;
    }

    public function count()
    {
        return count($this->widgetSettings);
    }
}
