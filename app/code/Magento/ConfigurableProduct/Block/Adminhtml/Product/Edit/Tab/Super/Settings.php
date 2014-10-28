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

/**
 * Create Configurable product Settings Tab Block
 */
namespace Magento\ConfigurableProduct\Block\Adminhtml\Product\Edit\Tab\Super;

use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Backend\Block\Widget\Form\Generic;

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
        array $data = array()
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
            array('label' => __('Generate Variations'), 'onclick' => $onclick, 'class' => 'save')
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
        $fieldset = $form->addFieldset('settings', array('legend' => __('Select Configurable Attributes')));

        $fieldset->addField(
            'configurable-attribute-selector',
            'text',
            array('label' => 'Select Attribute', 'title' => 'Select Attribute')
        );

        $product = $this->getProduct();
        $usedAttributes = $product->getTypeId() ==
            Configurable::TYPE_CODE ? $this->_configurableType->getUsedProductAttributes($product) : array();
        foreach ($usedAttributes as $attribute) {
            /** @var $attribute \Magento\Catalog\Model\Resource\Eav\Attribute */
            if ($this->_configurableType->canUseAttribute($attribute, $product)) {
                $fieldset->addField(
                    'attribute_' . $attribute->getAttributeId(),
                    'checkbox',
                    array(
                        'label' => $attribute->getFrontendLabel(),
                        'title' => $attribute->getFrontendLabel(),
                        'name' => 'attributes[]',
                        'class' => 'configurable-attribute-checkbox',
                        'value' => $attribute->getAttributeId(),
                        'checked' => true
                    )
                );
            }
        }

        $fieldset->addField('continue_button', 'note', array('text' => $this->getChildHtml('continue_button')));
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
        return $this->getUrl($this->getProduct()->getId() ? '*/*/edit' : '*/*/new', array('_current' => true));
    }

    /**
     * Retrieve Back URL
     *
     * @return string
     */
    public function getBackUrl()
    {
        return $this->getUrl('catalog/*/', array('set' => null, 'type' => null));
    }
}
