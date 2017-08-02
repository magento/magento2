<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Backend\Block\Widget\Form\Element;

use Magento\Framework\Data\Form\Element\AbstractElement;

/**
 * Backend image gallery item renderer
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 * @since 2.0.0
 */
class Gallery extends \Magento\Backend\Block\Template implements
    \Magento\Framework\Data\Form\Element\Renderer\RendererInterface
{
    /**
     * @var AbstractElement|null
     * @since 2.0.0
     */
    protected $_element = null;

    /**
     * @var string
     * @since 2.0.0
     */
    protected $_template = 'Magento_Backend::widget/form/element/gallery.phtml';

    /**
     * @param AbstractElement $element
     * @return string
     * @since 2.0.0
     */
    public function render(AbstractElement $element)
    {
        $this->setElement($element);
        return $this->toHtml();
    }

    /**
     * @param AbstractElement $element
     * @return $this
     * @since 2.0.0
     */
    public function setElement(AbstractElement $element)
    {
        $this->_element = $element;
        return $this;
    }

    /**
     * @return AbstractElement|null
     * @since 2.0.0
     */
    public function getElement()
    {
        return $this->_element;
    }

    /**
     * @return array
     * @since 2.0.0
     */
    public function getValues()
    {
        return $this->getElement()->getValue();
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    protected function _prepareLayout()
    {
        $this->addChild(
            'delete_button',
            \Magento\Backend\Block\Widget\Button::class,
            ['label' => __('Delete'), 'onclick' => "deleteImage(#image#)", 'class' => 'delete']
        );

        $this->addChild(
            'add_button',
            \Magento\Backend\Block\Widget\Button::class,
            ['label' => __('Add New Image'), 'onclick' => 'addNewImage()', 'class' => 'add']
        );
        return parent::_prepareLayout();
    }

    /**
     * @return string
     * @since 2.0.0
     */
    public function getAddButtonHtml()
    {
        return $this->getChildHtml('add_button');
    }

    /**
     * @param string $image
     * @return string|string[]
     * @since 2.0.0
     */
    public function getDeleteButtonHtml($image)
    {
        return str_replace('#image#', $image, $this->getChildHtml('delete_button'));
    }
}
