<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\DesignEditor\Model\Editor\Tools\QuickStyles;

/**
 * Quick style CSS renderer
 */
class Renderer
{
    /**
     * Quick style renderer factory
     *
     * @var \Magento\DesignEditor\Model\Editor\Tools\QuickStyles\Renderer\Factory
     */
    protected $_quickStyleFactory;

    /**
     * @param \Magento\DesignEditor\Model\Editor\Tools\QuickStyles\Renderer\Factory $factory
     */
    public function __construct(\Magento\DesignEditor\Model\Editor\Tools\QuickStyles\Renderer\Factory $factory)
    {
        $this->_quickStyleFactory = $factory;
    }

    /**
     * Render Quick Style CSS
     *
     * @param array $data
     * @return string
     */
    public function render($data)
    {
        $content = '';
        foreach ($data as $element) {
            $this->_rendererCssRecursively($element, $content);
        }
        return $content;
    }

    /**
     * Render CSS recursively
     *
     * @param array $data
     * @param string &$content
     * @return $this
     */
    protected function _rendererCssRecursively($data, &$content)
    {
        if (isset($data['components'])) {
            foreach ($data['components'] as $component) {
                $this->_rendererCssRecursively($component, $content);
            }
        } elseif (!empty($data['value']) && $data['value'] != $data['default'] && !empty($data['attribute']) ||
            empty($data['value']) && $this->_isBackgroundImage(
                $data
            )
        ) {
            $content .= $this->_quickStyleFactory->get($data['attribute'])->toCss($data) . "\n";
        }
        return $this;
    }

    /**
     * Override the parent's default value for this specific component.
     *
     * @param array $data
     * @return bool
     */
    protected function _isBackgroundImage($data)
    {
        return !empty($data['attribute']) &&
            $data['attribute'] === 'background-image' &&
            !empty($data['type']) &&
            $data['type'] === 'image-uploader' &&
            !empty($data['selector']) &&
            $data['selector'] === '.header';
    }
}
