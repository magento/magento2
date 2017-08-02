<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Backend\Block\Widget\Form\Renderer;

use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Data\Form\Element\Renderer\RendererInterface;

/**
 * Form element default renderer
 *
 * @api
 * @deprecated 2.2.0 in favour of UI component implementation
 * @since 2.0.0
 */
class Element extends \Magento\Backend\Block\Template implements RendererInterface
{
    /**
     * @var AbstractElement
     * @since 2.0.0
     */
    protected $_element;

    /**
     * @var string
     * @since 2.0.0
     */
    protected $_template = 'Magento_Backend::widget/form/renderer/element.phtml';

    /**
     * @return AbstractElement
     * @since 2.0.0
     */
    public function getElement()
    {
        return $this->_element;
    }

    /**
     * @param AbstractElement $element
     * @return string
     * @since 2.0.0
     */
    public function render(AbstractElement $element)
    {
        $this->_element = $element;
        return $this->toHtml();
    }
}
