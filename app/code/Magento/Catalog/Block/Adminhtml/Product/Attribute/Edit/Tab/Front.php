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
 * Product attribute add/edit form main tab
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Catalog\Block\Adminhtml\Product\Attribute\Edit\Tab;

use Magento\Backend\Block\Widget\Form;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Model\Config\Source\Yesno;

class Front extends Generic
{
    /**
     * @var Yesno
     */
    protected $_yesNo;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param Yesno $yesNo
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        Yesno $yesNo,
        array $data = array()
    ) {
        $this->_yesNo = $yesNo;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * {@inheritdoc}
     * @return $this
     */
    protected function _prepareForm()
    {
        $attributeObject = $this->_coreRegistry->registry('entity_attribute');

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create(
            array('data' => array('id' => 'edit_form', 'action' => $this->getData('action'), 'method' => 'post'))
        );

        $yesnoSource = $this->_yesNo->toOptionArray();

        $fieldset = $form->addFieldset(
            'front_fieldset',
            array('legend' => __('Frontend Properties'), 'collapsable' => $this->getRequest()->has('popup'))
        );

        $fieldset->addField(
            'is_searchable',
            'select',
            array(
                'name'     => 'is_searchable',
                'label'    => __('Use in Quick Search'),
                'title'    => __('Use in Quick Search'),
                'values'   => $yesnoSource,
            )
        );

        $fieldset->addField(
            'is_visible_in_advanced_search',
            'select',
            array(
                'name' => 'is_visible_in_advanced_search',
                'label' => __('Use in Advanced Search'),
                'title' => __('Use in Advanced Search'),
                'values' => $yesnoSource,
            )
        );

        $fieldset->addField(
            'is_comparable',
            'select',
            array(
                'name' => 'is_comparable',
                'label' => __('Comparable on Frontend'),
                'title' => __('Comparable on Frontend'),
                'values' => $yesnoSource,
            )
        );

        $this->_eventManager->dispatch('product_attribute_form_build_front_tab', array('form' => $form));

        $fieldset->addField(
            'is_used_for_promo_rules',
            'select',
            array(
                'name' => 'is_used_for_promo_rules',
                'label' => __('Use for Promo Rule Conditions'),
                'title' => __('Use for Promo Rule Conditions'),
                'values' => $yesnoSource,
            )
        );

        $fieldset->addField(
            'is_wysiwyg_enabled',
            'select',
            array(
                'name' => 'is_wysiwyg_enabled',
                'label' => __('Enable WYSIWYG'),
                'title' => __('Enable WYSIWYG'),
                'values' => $yesnoSource,
            )
        );

        $fieldset->addField(
            'is_html_allowed_on_front',
            'select',
            array(
                'name' => 'is_html_allowed_on_front',
                'label' => __('Allow HTML Tags on Frontend'),
                'title' => __('Allow HTML Tags on Frontend'),
                'values' => $yesnoSource,
            )
        );
        if (!$attributeObject->getId() || $attributeObject->getIsWysiwygEnabled()) {
            $attributeObject->setIsHtmlAllowedOnFront(1);
        }

        $fieldset->addField(
            'is_visible_on_front',
            'select',
            array(
                'name' => 'is_visible_on_front',
                'label' => __('Visible on Catalog Pages on Frontend'),
                'title' => __('Visible on Catalog Pages on Frontend'),
                'values' => $yesnoSource
            )
        );

        $fieldset->addField(
            'used_in_product_listing',
            'select',
            array(
                'name' => 'used_in_product_listing',
                'label' => __('Used in Product Listing'),
                'title' => __('Used in Product Listing'),
                'note' => __('Depends on design theme'),
                'values' => $yesnoSource
            )
        );

        $fieldset->addField(
            'used_for_sort_by',
            'select',
            array(
                'name' => 'used_for_sort_by',
                'label' => __('Used for Sorting in Product Listing'),
                'title' => __('Used for Sorting in Product Listing'),
                'note' => __('Depends on design theme'),
                'values' => $yesnoSource
            )
        );

        $this->_eventManager->dispatch(
            'adminhtml_catalog_product_attribute_edit_frontend_prepare_form',
            array('form' => $form, 'attribute' => $attributeObject)
        );

        // define field dependencies
        $this->setChild(
            'form_after',
            $this->getLayout()->createBlock(
                'Magento\Backend\Block\Widget\Form\Element\Dependence'
            )->addFieldMap(
                "is_wysiwyg_enabled",
                'wysiwyg_enabled'
            )->addFieldMap(
                "is_html_allowed_on_front",
                'html_allowed_on_front'
            )->addFieldMap(
                "frontend_input",
                'frontend_input_type'
            )->addFieldDependence(
                'wysiwyg_enabled',
                'frontend_input_type',
                'textarea'
            )->addFieldDependence(
                'html_allowed_on_front',
                'wysiwyg_enabled',
                '0'
            )
        );

        $form->setValues($attributeObject->getData());
        $this->setForm($form);
        return parent::_prepareForm();
    }
}
