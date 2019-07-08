<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Adminhtml catalog product edit action attributes update tab block
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
declare(strict_types=1);

namespace Magento\Catalog\Block\Adminhtml\Product\Edit\Action\Attribute\Tab;

use Magento\Framework\Data\Form\Element\AbstractElement;

/**
 * Attributes tab block
 *
 * @api
 * @SuppressWarnings(PHPMD.DepthOfInheritance)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @since 100.0.2
 */
class Attributes extends \Magento\Catalog\Block\Adminhtml\Form implements
    \Magento\Backend\Block\Widget\Tab\TabInterface
{
    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $_productFactory;

    /**
     * @var \Magento\Catalog\Helper\Product\Edit\Action\Attribute
     */
    protected $_attributeAction;

    /** @var array */
    private $excludeFields;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Magento\Catalog\Helper\Product\Edit\Action\Attribute $attributeAction
     * @param array $data
     * @param array|null $excludeFields
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Catalog\Helper\Product\Edit\Action\Attribute $attributeAction,
        array $data = [],
        array $excludeFields = null
    ) {
        $this->_attributeAction = $attributeAction;
        $this->_productFactory = $productFactory;
        $this->excludeFields = $excludeFields ?: [];

        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Prepares form
     *
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareForm(): void
    {
        $this->setFormExcludedFieldList($this->getExcludedFields());
        $this->_eventManager->dispatch(
            'adminhtml_catalog_product_form_prepare_excluded_field_list',
            ['object' => $this]
        );

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();
        $fieldset = $form->addFieldset('fields', ['legend' => __('Attributes')]);
        $attributes = $this->getAttributes();
        /**
         * Initialize product object as form property
         * for using it in elements generation
         */
        $form->setDataObject($this->_productFactory->create());
        $this->_setFieldset($attributes, $fieldset, $this->getFormExcludedFieldList());
        $form->setFieldNameSuffix('attributes');
        $this->setForm($form);
    }

    /**
     * Retrieve attributes for product mass update
     *
     * @return \Magento\Framework\DataObject[]
     */
    public function getAttributes()
    {
        return $this->_attributeAction->getAttributes()->getItems();
    }

    /**
     * Additional element types for product attributes
     *
     * @return array
     */
    protected function _getAdditionalElementTypes()
    {
        return [
            'price' => \Magento\Catalog\Block\Adminhtml\Product\Helper\Form\Price::class,
            'weight' => \Magento\Catalog\Block\Adminhtml\Product\Helper\Form\Weight::class,
            'image' => \Magento\Catalog\Block\Adminhtml\Product\Helper\Form\Image::class,
            'boolean' => \Magento\Catalog\Block\Adminhtml\Product\Helper\Form\Boolean::class
        ];
    }

    /**
     * Custom additional element html
     *
     * @param AbstractElement $element
     * @return string
     */
    protected function _getAdditionalElementHtml($element)
    {
        // Add name attribute to checkboxes that correspond to multiselect elements
        $nameAttributeHtml = $element->getExtType() === 'multiple' ? 'name="' . $element->getId() . '_checkbox"' : '';
        $elementId = $element->getId();
        $dataAttribute = "data-disable='{$elementId}'";
        $dataCheckboxName = "toggle_" . "{$elementId}";
        $checkboxLabel = __('Change');
        // @codingStandardsIgnoreStart
        $html = <<<HTML
<span class="attribute-change-checkbox">
    <input type="checkbox" id="$dataCheckboxName" name="$dataCheckboxName" class="checkbox" $nameAttributeHtml onclick="toogleFieldEditMode(this, '{$elementId}')" $dataAttribute />
    <label class="label" for="$dataCheckboxName">
        {$checkboxLabel}
    </label>
</span>
HTML;
        if ($elementId === 'weight') {
            $html .= <<<HTML
<script>require(['Magento_Catalog/js/product/weight-handler'], function (weightHandle) {
    weightHandle.hideWeightSwitcher();
});</script>
HTML;
            // @codingStandardsIgnoreEnd
        }
        return $html;
    }

    /**
     * Returns tab label
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabLabel()
    {
        return __('Attributes');
    }

    /**
     * Return Tab title
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabTitle()
    {
        return __('Attributes');
    }

    /**
     * Can show tab in tabs
     *
     * @return bool
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * Tab not hidden
     *
     * @return bool
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * Returns excluded fields
     *
     * @return array
     */
    private function getExcludedFields(): array
    {
        return $this->excludeFields;
    }
}
