<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Product attribute add/edit form main tab
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Catalog\Block\Adminhtml\Product\Attribute\Edit\Tab;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Form;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Config\Model\Config\Source\Yesno;
use Magento\Catalog\Model\Entity\Attribute;
use Magento\Eav\Block\Adminhtml\Attribute\PropertyLocker;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Registry;

/**
 * @api
 * @since 100.0.2
 */
class Front extends Generic
{
    /**
     * @var Yesno
     */
    protected $_yesNo;

    /**
     * @var PropertyLocker
     */
    private $propertyLocker;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param FormFactory $formFactory
     * @param Yesno $yesNo
     * @param PropertyLocker $propertyLocker
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        Yesno $yesNo,
        PropertyLocker $propertyLocker,
        array $data = []
    ) {
        $this->_yesNo = $yesNo;
        $this->propertyLocker = $propertyLocker;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * {@inheritdoc}
     * @return $this
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _prepareForm()
    {
        /** @var Attribute $attributeObject */
        $attributeObject = $this->_coreRegistry->registry('entity_attribute');

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create(
            ['data' => ['id' => 'edit_form', 'action' => $this->getData('action'), 'method' => 'post']]
        );

        $yesnoSource = $this->_yesNo->toOptionArray();

        $fieldset = $form->addFieldset(
            'front_fieldset',
            ['legend' => __('Storefront Properties'), 'collapsable' => $this->getRequest()->has('popup')]
        );

        $fieldset->addField(
            'is_searchable',
            'select',
            [
                'name'     => 'is_searchable',
                'label'    => __('Use in Search'),
                'title'    => __('Use in Search'),
                'values'   => $yesnoSource,
            ]
        );

        $fieldset->addField(
            'is_visible_in_advanced_search',
            'select',
            [
                'name' => 'is_visible_in_advanced_search',
                'label' => __('Visible in Advanced Search'),
                'title' => __('Visible in Advanced Search'),
                'values' => $yesnoSource,
            ]
        );

        $fieldset->addField(
            'is_comparable',
            'select',
            [
                'name' => 'is_comparable',
                'label' => __('Comparable on Storefront'),
                'title' => __('Comparable on Storefront'),
                'values' => $yesnoSource,
            ]
        );

        $this->_eventManager->dispatch('product_attribute_form_build_front_tab', ['form' => $form]);

        $fieldset->addField(
            'is_used_for_promo_rules',
            'select',
            [
                'name' => 'is_used_for_promo_rules',
                'label' => __('Use for Promo Rule Conditions'),
                'title' => __('Use for Promo Rule Conditions'),
                'values' => $yesnoSource,
            ]
        );

        $fieldset->addField(
            'is_html_allowed_on_front',
            'select',
            [
                'name' => 'is_html_allowed_on_front',
                'label' => __('Allow HTML Tags on Storefront'),
                'title' => __('Allow HTML Tags on Storefront'),
                'values' => $yesnoSource,
            ]
        );
        if (!$attributeObject->getId() || $attributeObject->getIsWysiwygEnabled()) {
            $attributeObject->setIsHtmlAllowedOnFront(1);
        }

        $fieldset->addField(
            'is_visible_on_front',
            'select',
            [
                'name' => 'is_visible_on_front',
                'label' => __('Visible on Catalog Pages on Storefront'),
                'title' => __('Visible on Catalog Pages on Storefront'),
                'values' => $yesnoSource
            ]
        );

        $fieldset->addField(
            'used_in_product_listing',
            'select',
            [
                'name' => 'used_in_product_listing',
                'label' => __('Used in Product Listing'),
                'title' => __('Used in Product Listing'),
                'note' => __('Depends on design theme.'),
                'values' => $yesnoSource
            ]
        );

        $fieldset->addField(
            'used_for_sort_by',
            'select',
            [
                'name' => 'used_for_sort_by',
                'label' => __('Used for Sorting in Product Listing'),
                'title' => __('Used for Sorting in Product Listing'),
                'note' => __('Depends on design theme.'),
                'values' => $yesnoSource
            ]
        );

        $this->_eventManager->dispatch(
            'adminhtml_catalog_product_attribute_edit_frontend_prepare_form',
            ['form' => $form, 'attribute' => $attributeObject]
        );

        // define field dependencies
        $this->setChild(
            'form_after',
            $this->getLayout()->createBlock(
                \Magento\Backend\Block\Widget\Form\Element\Dependence::class
            )->addFieldMap(
                "is_html_allowed_on_front",
                'html_allowed_on_front'
            )->addFieldMap(
                "frontend_input",
                'frontend_input_type'
            )->addFieldMap(
                "is_searchable",
                'searchable'
            )->addFieldMap(
                "is_visible_in_advanced_search",
                'advanced_search'
            )->addFieldDependence(
                'advanced_search',
                'searchable',
                '1'
            )
        );

        $this->setForm($form);
        $form->setValues($attributeObject->getData());
        $this->propertyLocker->lock($form);
        return parent::_prepareForm();
    }
}
