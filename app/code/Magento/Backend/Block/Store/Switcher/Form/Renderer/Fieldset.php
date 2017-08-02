<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Backend\Block\Store\Switcher\Form\Renderer;

use \Magento\Framework\Data\Form\Element\Renderer\RendererInterface;

/**
 * Form fieldset renderer
 * @api
 * @since 2.0.0
 */
class Fieldset extends \Magento\Backend\Block\Template implements RendererInterface
{
    /**
     * Form element which re-rendering
     *
     * @var \Magento\Framework\Data\Form\Element\Fieldset
     * @since 2.0.0
     */
    protected $_element;

    /**
     * @var string
     * @since 2.0.0
     */
    protected $_template = 'store/switcher/form/renderer/fieldset.phtml';

    /**
     * Retrieve an element
     *
     * @return \Magento\Framework\Data\Form\Element\Fieldset
     * @since 2.0.0
     */
    public function getElement()
    {
        return $this->_element;
    }

    /**
     * Render element
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     * @since 2.0.0
     */
    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $this->_element = $element;
        return $this->toHtml();
    }

    /**
     * Return html for store switcher hint
     *
     * @return string
     * @since 2.0.0
     */
    public function getHintHtml()
    {
        /** @var $storeSwitcher \Magento\Backend\Block\Store\Switcher */
        $storeSwitcher = $this->_layout->getBlockSingleton(\Magento\Backend\Block\Store\Switcher::class);
        return $storeSwitcher->getHintHtml();
    }
}
