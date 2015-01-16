<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Block\Widget\Form\Renderer\Fieldset;

use Magento\Framework\Data\Form\Element\AbstractElement;

/**
 * Fieldset element renderer
 *
 * @author      Magento Core Team <core@magentocommerce.com>
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
    protected $_template = 'Magento_Backend::widget/form/renderer/fieldset/element.phtml';

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
