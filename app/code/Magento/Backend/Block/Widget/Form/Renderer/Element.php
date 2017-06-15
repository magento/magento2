<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Backend\Block\Widget\Form\Renderer;

use Magento\Framework\Data\Form\Element\AbstractElement;

/**
 * Form element default renderer
 *
 * @api
 * @deprecated in favour of UI component implementation
 */
class Element extends \Magento\Backend\Block\Template implements \Magento\Framework\Data\Form\Element\Renderer\RendererInterface
{
    /**
     * @var AbstractElement
     */
    protected $_element;

    /**
     * @var string
     */
    protected $_template = 'Magento_Backend::widget/form/renderer/element.phtml';

    /**
     * @return AbstractElement
     */
    public function getElement()
    {
        return $this->_element;
    }

    /**
     * @param AbstractElement $element
     * @return string
     */
    public function render(AbstractElement $element)
    {
        $this->_element = $element;
        return $this->toHtml();
    }
}
