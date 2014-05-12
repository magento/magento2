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
namespace Magento\SalesRule\Block\Adminhtml\Promo\Quote\Edit\Tab\Coupons;

/**
 * Coupons generation parameters form
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Form extends \Magento\Backend\Block\Widget\Form\Generic
{
    /**
     * Sales rule coupon
     *
     * @var \Magento\SalesRule\Helper\Coupon
     */
    protected $_salesRuleCoupon = null;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\SalesRule\Helper\Coupon $salesRuleCoupon
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\SalesRule\Helper\Coupon $salesRuleCoupon,
        array $data = array()
    ) {
        $this->_salesRuleCoupon = $salesRuleCoupon;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Prepare coupon codes generation parameters form
     *
     * @return $this
     */
    protected function _prepareForm()
    {
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();

        /**
         * @var \Magento\SalesRule\Helper\Coupon $couponHelper
         */
        $couponHelper = $this->_salesRuleCoupon;

        $model = $this->_coreRegistry->registry('current_promo_quote_rule');
        $ruleId = $model->getId();

        $form->setHtmlIdPrefix('coupons_');

        $gridBlock = $this->getLayout()->getBlock('promo_quote_edit_tab_coupons_grid');
        $gridBlockJsObject = '';
        if ($gridBlock) {
            $gridBlockJsObject = $gridBlock->getJsObjectName();
        }

        $fieldset = $form->addFieldset('information_fieldset', array('legend' => __('Coupons Information')));
        $fieldset->addClass('ignore-validate');

        $fieldset->addField('rule_id', 'hidden', array('name' => 'rule_id', 'value' => $ruleId));

        $fieldset->addField(
            'qty',
            'text',
            array(
                'name' => 'qty',
                'label' => __('Coupon Qty'),
                'title' => __('Coupon Qty'),
                'required' => true,
                'class' => 'validate-digits validate-greater-than-zero'
            )
        );

        $fieldset->addField(
            'length',
            'text',
            array(
                'name' => 'length',
                'label' => __('Code Length'),
                'title' => __('Code Length'),
                'required' => true,
                'note' => __('Excluding prefix, suffix and separators.'),
                'value' => $couponHelper->getDefaultLength(),
                'class' => 'validate-digits validate-greater-than-zero'
            )
        );

        $fieldset->addField(
            'format',
            'select',
            array(
                'label' => __('Code Format'),
                'name' => 'format',
                'options' => $couponHelper->getFormatsList(),
                'required' => true,
                'value' => $couponHelper->getDefaultFormat()
            )
        );

        $fieldset->addField(
            'prefix',
            'text',
            array(
                'name' => 'prefix',
                'label' => __('Code Prefix'),
                'title' => __('Code Prefix'),
                'value' => $couponHelper->getDefaultPrefix()
            )
        );

        $fieldset->addField(
            'suffix',
            'text',
            array(
                'name' => 'suffix',
                'label' => __('Code Suffix'),
                'title' => __('Code Suffix'),
                'value' => $couponHelper->getDefaultSuffix()
            )
        );

        $fieldset->addField(
            'dash',
            'text',
            array(
                'name' => 'dash',
                'label' => __('Dash Every X Characters'),
                'title' => __('Dash Every X Characters'),
                'note' => __('If empty no separation.'),
                'value' => $couponHelper->getDefaultDashInterval(),
                'class' => 'validate-digits'
            )
        );

        $idPrefix = $form->getHtmlIdPrefix();
        $generateUrl = $this->getGenerateUrl();

        $fieldset->addField(
            'generate_button',
            'note',
            array(
                'text' => $this->getButtonHtml(
                    __('Generate'),
                    "generateCouponCodes('{$idPrefix}' ,'{$generateUrl}', '{$gridBlockJsObject}')",
                    'generate'
                )
            )
        );

        $this->setForm($form);

        $this->_eventManager->dispatch(
            'adminhtml_promo_quote_edit_tab_coupons_form_prepare_form',
            array('form' => $form)
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
