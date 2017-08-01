<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Backend\Block\Widget\Form\Renderer;

use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Data\Form\Element\Renderer\RendererInterface;

/**
 * Form fieldset default renderer
 *
 * @api
 * @author      Magento Core Team <core@magentocommerce.com>
 * @api
 * @since 2.0.0
 */
class Fieldset extends \Magento\Backend\Block\Template implements RendererInterface
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
    protected $_template = 'Magento_Backend::widget/form/renderer/fieldset.phtml';

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
