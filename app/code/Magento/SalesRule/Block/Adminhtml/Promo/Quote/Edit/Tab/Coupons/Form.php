<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SalesRule\Block\Adminhtml\Promo\Quote\Edit\Tab\Coupons;

use Magento\Backend\Block\Template\Context as TemplateContext;
use Magento\Backend\Block\Widget\Form\Generic as FormGeneric;
use Magento\Framework\Data\Form as FormData;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Registry;
use Magento\SalesRule\Helper\Coupon as CouponHelper;
use Magento\SalesRule\Model\RegistryConstants;

/**
 * Coupons generation parameters form
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 *
 * Class \Magento\SalesRule\Block\Adminhtml\Promo\Quote\Edit\Tab\Coupons\Form
 */
class Form extends FormGeneric
{
    /**
     * Sales rule coupon
     *
     * @var CouponHelper
     */
    protected $_salesRuleCoupon = null;

    /**
     * @param TemplateContext $context
     * @param Registry $registry
     * @param FormFactory $formFactory
     * @param CouponHelper $salesRuleCoupon
     * @param array $data
     */
    public function __construct(
        TemplateContext $context,
        Registry $registry,
        FormFactory $formFactory,
        CouponHelper $salesRuleCoupon,
        array $data = []
    ) {
        $this->_salesRuleCoupon = $salesRuleCoupon;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Prepare coupon codes generation parameters form
     *
     * @return $this
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _prepareForm()
    {
        /** @var FormData $form */
        $form = $this->_formFactory->create();

        /** @var CouponHelper $couponHelper */
        $couponHelper = $this->_salesRuleCoupon;

        $model = $this->_coreRegistry->registry(RegistryConstants::CURRENT_SALES_RULE);
        $ruleId = $model->getId();

        $form->setHtmlIdPrefix('coupons_');

        $gridBlock = $this->getLayout()->getBlock('promo_quote_edit_tab_coupons_grid');
        $gridBlockJsObject = '';
        if ($gridBlock) {
            $gridBlockJsObject = $gridBlock->getJsObjectName();
        }

        $fieldset = $form->addFieldset('information_fieldset', []);
        $fieldset->addClass('ignore-validate');

        $fieldset->addField('rule_id', 'hidden', ['name' => 'rule_id', 'value' => $ruleId]);

        $fieldset->addField(
            'qty',
            'text',
            [
                'name' => 'qty',
                'label' => __('Coupon Qty'),
                'title' => __('Coupon Qty'),
                'required' => true,
                'class' => 'validate-digits validate-greater-than-zero',
                'onchange' => 'window.validateCouponGenerate(this)'
            ]
        );

        $fieldset->addField(
            'length',
            'text',
            [
                'name' => 'length',
                'label' => __('Code Length'),
                'title' => __('Code Length'),
                'required' => true,
                'note' => __('Excluding prefix, suffix and separators.'),
                'value' => $couponHelper->getDefaultLength(),
                'class' => 'validate-digits validate-greater-than-zero',
                'onchange' => 'window.validateCouponGenerate(this)'
            ]
        );

        $fieldset->addField(
            'format',
            'select',
            [
                'label' => __('Code Format'),
                'name' => 'format',
                'options' => $couponHelper->getFormatsList(),
                'required' => true,
                'value' => $couponHelper->getDefaultFormat(),
                'onchange' => 'window.validateCouponGenerate(this)'
            ]
        );

        $fieldset->addField(
            'prefix',
            'text',
            [
                'name' => 'prefix',
                'label' => __('Code Prefix'),
                'title' => __('Code Prefix'),
                'value' => $couponHelper->getDefaultPrefix()
            ]
        );

        $fieldset->addField(
            'suffix',
            'text',
            [
                'name' => 'suffix',
                'label' => __('Code Suffix'),
                'title' => __('Code Suffix'),
                'value' => $couponHelper->getDefaultSuffix()
            ]
        );

        $fieldset->addField(
            'dash',
            'text',
            [
                'name' => 'dash',
                'label' => __('Dash Every X Characters'),
                'title' => __('Dash Every X Characters'),
                'note' => __('If empty no separation.'),
                'value' => $couponHelper->getDefaultDashInterval(),
                'class' => 'validate-digits',
                'onchange' => 'window.validateCouponGenerate(this)'
            ]
        );

        $idPrefix = $form->getHtmlIdPrefix();
        $generateUrl = $this->getGenerateUrl();

        $fieldset->addField(
            'generate_button',
            'note',
            [
                'text' => $this->getButtonHtml(
                    __('Generate'),
                    "generateCouponCodes('{$idPrefix}' ,'{$generateUrl}', '{$gridBlockJsObject}')",
                    'generate'
                )
            ]
        );

        $this->setForm($form);

        $this->_eventManager->dispatch(
            'adminhtml_promo_quote_edit_tab_coupons_form_prepare_form',
            ['form' => $form]
        );

        return parent::_prepareForm();
    }

    /**
     * Retrieve URL to Generate Action
     *
     * @return string
     */
    public function getGenerateUrl()
    {
        return $this->getUrl('sales_rule/*/generate');
    }
}
