<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Backend\Block\Widget\Form\Element;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Json\Helper\Data as JsonHelper;
use Magento\Backend\Block\Template\Context;

/**
 * Backend image gallery item renderer
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Gallery extends \Magento\Backend\Block\Template implements
    \Magento\Framework\Data\Form\Element\Renderer\RendererInterface
{
    /**
     * @var AbstractElement|null
     */
    protected $_element = null;

    /**
     * @var string
     */
    protected $_template = 'Magento_Backend::widget/form/element/gallery.phtml';

    /**
     * @param Context $context
     * @param array $data
     */
    public function __construct(Context $context, array $data = [])
    {
        $data['jsonHelper'] = ObjectManager::getInstance()->get(JsonHelper::class);
        parent::__construct($context, $data);
    }

    /**
     * Renderer.
     *
     * @param AbstractElement $element
     * @return string
     */
    public function render(AbstractElement $element)
    {
        $this->setElement($element);
        return $this->toHtml();
    }

    /**
     * Set element.
     *
     * @param AbstractElement $element
     * @return $this
     */
    public function setElement(AbstractElement $element)
    {
        $this->_element = $element;
        return $this;
    }

    /**
     * Get element.
     *
     * @return AbstractElement|null
     */
    public function getElement()
    {
        return $this->_element;
    }

    /**
     * Get value.
     *
     * @return array
     */
    public function getValues()
    {
        return $this->getElement()->getValue();
    }

    /**
     * @inheritdoc
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
     * Return add button.
     *
     * @return string
     */
    public function getAddButtonHtml()
    {
        return $this->getChildHtml('add_button');
    }

    /**
     * Return delete button.
     *
     * @param string $image
     * @return string|string[]
     */
    public function getDeleteButtonHtml($image)
    {
        return str_replace('#image#', $image, $this->getChildHtml('delete_button'));
    }
}
