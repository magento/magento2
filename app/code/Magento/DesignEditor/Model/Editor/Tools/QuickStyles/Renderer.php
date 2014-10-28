<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
