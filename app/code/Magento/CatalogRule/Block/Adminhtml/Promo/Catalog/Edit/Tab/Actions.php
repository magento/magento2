<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogRule\Block\Adminhtml\Promo\Catalog\Edit\Tab;

use Magento\Backend\Block\Widget\Form;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;

class Actions extends Generic implements TabInterface
{
    /**
     * Prepare content for tab
     *
     * @return \Magento\Framework\Phrase
     * @codeCoverageIgnore
     */
    public function getTabLabel()
    {
        return __('Actions');
    }

    /**
     * Prepare title for tab
     *
     * @return \Magento\Framework\Phrase
     * @codeCoverageIgnore
     */
    public function getTabTitle()
    {
        return __('Actions');
    }

    /**
     * Returns status flag about this tab can be showen or not
     *
     * @return bool
     * @codeCoverageIgnore
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * Returns status flag about this tab hidden or not
     *
     * @return bool
     * @codeCoverageIgnore
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * @return Form
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _prepareForm()
    {
        $model = $this->_coreRegistry->registry('current_promo_catalog_rule');

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('rule_');

        $fieldset = $form->addFieldset(
            'action_fieldset',
            ['legend' => __('Pricing Structure Rules')]
        );

        $fieldset->addField(
            'simple_action',
            'select',
            [
                'label' => __('Apply'),
                'name' => 'simple_action',
                'options' => [
                    'by_percent' => __('Apply as percentage of original'),
                    'by_fixed' => __('Apply as fixed amount'),
                    'to_percent' => __('Adjust final price to this percentage'),
                    'to_fixed' => __('Adjust final price to discount value'),
                ]
            ]
        );

        $fieldset->addField(
            'discount_amount',
            'text',
            [
                'name' => 'discount_amount',
                'required' => true,
                'class' => 'validate-not-negative-number',
                'label' => __('Discount Amount')
            ]
        );

        $fieldset->addField(
            'sub_is_enable',
            'select',
            [
                'name' => 'sub_is_enable',
                'label' => __('Subproduct discounts'),
                'title' => __('Subproduct discounts'),
                'onchange' => 'hideShowSubproductOptions(this);',
                'values' => [0 => __('No'), 1 => __('Yes')]
            ]
        );

        $fieldset->addField(
            'sub_simple_action',
            'select',
            [
                'label' => __('Apply'),
                'name' => 'sub_simple_action',
                'options' => [
                    'by_percent' => __('Apply as percentage of original'),
                    'by_fixed' => __('Apply as fixed amount'),
                    'to_percent' => __('Adjust final price to this percentage'),
                    'to_fixed' => __('Adjust final price to discount value'),
                ]
            ]
        );

        $fieldset->addField(
            'sub_discount_amount',
            'text',
            [
                'name' => 'sub_discount_amount',
                'required' => true,
                'class' => 'validate-not-negative-number',
                'label' => __('Discount Amount')
            ]
        );

        $fieldset->addField(
            'stop_rules_processing',
            'select',
            [
                'label' => __('Discard subsequent rules'),
                'title' => __('Discard subsequent rules'),
                'name' => 'stop_rules_processing',
                'options' => ['1' => __('Yes'), '0' => __('No')]
            ]
        );

        $form->setValues($model->getData());

        //$form->setUseContainer(true);

        if ($model->isReadonly()) {
            foreach ($fieldset->getElements() as $element) {
                $element->setReadonly(true, true);
            }
        }

        $this->setForm($form);

        return parent::_prepareForm();
    }
}
