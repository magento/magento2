<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Block\Adminhtml\Report\Filter\Form;

/**
 * Sales Adminhtml report filter form for coupons report
 *
 * @api
 * @author     Magento Core Team <core@magentocommerce.com>
 * @SuppressWarnings(PHPMD.DepthOfInheritance)
 * @since 2.0.0
 */
class Coupon extends \Magento\Sales\Block\Adminhtml\Report\Filter\Form
{
    /**
     * Flag that keep info should we render specific dependent element or not
     *
     * @var bool
     * @since 2.0.0
     */
    protected $_renderDependentElement = false;

    /**
     * Rule factory
     *
     * @var \Magento\SalesRule\Model\ResourceModel\Report\RuleFactory
     * @since 2.0.0
     */
    protected $_reportRule;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Sales\Model\Order\ConfigFactory $orderConfig
     * @param \Magento\SalesRule\Model\ResourceModel\Report\RuleFactory $reportRule
     * @param array $data
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Sales\Model\Order\ConfigFactory $orderConfig,
        \Magento\SalesRule\Model\ResourceModel\Report\RuleFactory $reportRule,
        array $data = []
    ) {
        $this->_reportRule = $reportRule;
        parent::__construct($context, $registry, $formFactory, $orderConfig, $data);
    }

    /**
     * Prepare form
     *
     * @return $this
     * @since 2.0.0
     */
    protected function _prepareForm()
    {
        parent::_prepareForm();

        /** @var \Magento\Framework\Data\Form\Element\Fieldset $fieldset */
        $fieldset = $this->getForm()->getElement('base_fieldset');

        if (is_object($fieldset) && $fieldset instanceof \Magento\Framework\Data\Form\Element\Fieldset) {
            $fieldset->addField(
                'price_rule_type',
                'select',
                [
                    'name' => 'price_rule_type',
                    'options' => [__('Any'), __('Specified')],
                    'label' => __('Cart Price Rule')
                ]
            );

            $rulesList = $this->_reportRule->create()->getUniqRulesNamesList();

            $rulesListOptions = [];

            foreach ($rulesList as $key => $ruleName) {
                $rulesListOptions[] = ['label' => $ruleName, 'value' => $key, 'title' => $ruleName];
            }

            $fieldset->addField(
                'rules_list',
                'multiselect',
                [
                    'name' => 'rules_list',
                    'label' => '',
                    'values' => $rulesListOptions,
                    'display' => 'none'
                ],
                'price_rule_type'
            );

            $this->_renderDependentElement = true;
        }

        return $this;
    }

    /**
     * Processing block html after rendering
     *
     * @param string $html
     * @return string
     * @since 2.0.0
     */
    protected function _afterToHtml($html)
    {
        if ($this->_renderDependentElement) {
            $form = $this->getForm();
            $htmlIdPrefix = $form->getHtmlIdPrefix();

            /**
             * Form template has possibility to render child block 'form_after', but we can't use it because parent
             * form creates appropriate child block and uses this alias. In this case we can't use the same alias
             * without core logic changes, that's why the code below was moved inside method '_afterToHtml'.
             */
            /** @var $formAfterBlock \Magento\Backend\Block\Widget\Form\Element\Dependence */
            $formAfterBlock = $this->getLayout()->createBlock(
                \Magento\Backend\Block\Widget\Form\Element\Dependence::class,
                'adminhtml.block.widget.form.element.dependence'
            );
            $formAfterBlock->addFieldMap(
                $htmlIdPrefix . 'price_rule_type',
                'price_rule_type'
            )->addFieldMap(
                $htmlIdPrefix . 'rules_list',
                'rules_list'
            )->addFieldDependence(
                'rules_list',
                'price_rule_type',
                '1'
            );
            $html = $html . $formAfterBlock->toHtml();
        }

        return $html;
    }
}
