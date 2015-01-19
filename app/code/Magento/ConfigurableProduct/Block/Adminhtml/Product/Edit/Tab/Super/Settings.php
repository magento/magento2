<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Create Configurable product Settings Tab Block
 */
namespace Magento\ConfigurableProduct\Block\Adminhtml\Product\Edit\Tab\Super;

use Magento\Backend\Block\Widget\Form\Generic;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;

class Settings extends Generic
{
    /**
     * @var \Magento\ConfigurableProduct\Model\Product\Type\Configurable
     */
    protected $_configurableType;

    /**
     * @var \Magento\Core\Helper\Data
     */
    protected $_coreHelper;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\ConfigurableProduct\Model\Product\Type\Configurable $configurableType
     * @param \Magento\Core\Helper\Data $coreHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\ConfigurableProduct\Model\Product\Type\Configurable $configurableType,
        \Magento\Core\Helper\Data $coreHelper,
        array $data = []
    ) {
        $this->_coreHelper = $coreHelper;
        $this->_configurableType = $configurableType;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Prepare block children and data
     *
     * @return void
     */
    protected function _prepareLayout()
    {
        $onclick = "jQuery('[data-form=edit-product]').attr('action', " . $this->_coreHelper->jsonEncode(
            $this->getContinueUrl()
        ) . ").addClass('ignore-validate').submit();";
        $this->addChild(
            'continue_button',
            'Magento\Backend\Block\Widget\Button',
            ['label' => __('Generate Variations'), 'onclick' => $onclick, 'class' => 'save']
        );
        parent::_prepareLayout();
    }

    /**
     * Retrieve currently edited product object
     *
     * @return array|null
     */
    public function getProduct()
    {
        return $this->_coreRegistry->registry('current_product');
    }

    /**
     * Prepare form before rendering HTML
     *
     * @return $this
     */
    protected function _prepareForm()
    {
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();
        $fieldset = $form->addFieldset('settings', ['legend' => __('Select Configurable Attributes')]);

        $fieldset->addField(
            'configurable-attribute-selector',
            'text',
            ['label' => 'Select Attribute', 'title' => 'Select Attribute']
        );

        $product = $this->getProduct();
        $usedAttributes = $product->getTypeId() ==
            Configurable::TYPE_CODE ? $this->_configurableType->getUsedProductAttributes($product) : [];
        foreach ($usedAttributes as $attribute) {
            /** @var $attribute \Magento\Catalog\Model\Resource\Eav\Attribute */
            if ($this->_configurableType->canUseAttribute($attribute, $product)) {
                $fieldset->addField(
                    'attribute_' . $attribute->getAttributeId(),
                    'checkbox',
                    [
                        'label' => $attribute->getFrontendLabel(),
                        'title' => $attribute->getFrontendLabel(),
                        'name' => 'attributes[]',
                        'class' => 'configurable-attribute-checkbox',
                        'value' => $attribute->getAttributeId(),
                        'checked' => true
                    ]
                );
            }
        }

        $fieldset->addField('continue_button', 'note', ['text' => $this->getChildHtml('continue_button')]);
        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * Retrieve Continue URL
     *
     * @return string
     */
    public function getContinueUrl()
    {
        return $this->getUrl($this->getProduct()->getId() ? '*/*/edit' : '*/*/new', ['_current' => true]);
    }

    /**
     * Retrieve Back URL
     *
     * @return string
     */
    public function getBackUrl()
    {
        return $this->getUrl('catalog/*/', ['set' => null, 'type' => null]);
    }
}
