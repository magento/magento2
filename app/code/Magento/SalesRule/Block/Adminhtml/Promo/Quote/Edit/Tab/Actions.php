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
namespace Magento\SalesRule\Block\Adminhtml\Promo\Quote\Edit\Tab;

class Actions extends \Magento\Backend\Block\Widget\Form\Generic implements
    \Magento\Backend\Block\Widget\Tab\TabInterface
{
    /**
     * Core registry
     *
     * @var \Magento\Backend\Block\Widget\Form\Renderer\Fieldset
     */
    protected $_rendererFieldset;

    /**
     * @var \Magento\Rule\Block\Actions
     */
    protected $_ruleActions;

    /**
     * @var \Magento\Backend\Model\Config\Source\Yesno
     */
    protected $_sourceYesno;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Backend\Model\Config\Source\Yesno $sourceYesno
     * @param \Magento\Rule\Block\Actions $ruleActions
     * @param \Magento\Backend\Block\Widget\Form\Renderer\Fieldset $rendererFieldset
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Backend\Model\Config\Source\Yesno $sourceYesno,
        \Magento\Rule\Block\Actions $ruleActions,
        \Magento\Backend\Block\Widget\Form\Renderer\Fieldset $rendererFieldset,
        array $data = array()
    ) {
        $this->_rendererFieldset = $rendererFieldset;
        $this->_ruleActions = $ruleActions;
        $this->_sourceYesno = $sourceYesno;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function getTabLabel()
    {
        return __('Actions');
    }

    /**
     * {@inheritdoc}
     */
    public function getTabTitle()
    {
        return __('Actions');
    }

    /**
     * {@inheritdoc}
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * Prepare form before rendering HTML
     *
     * @return $this
     */
    protected function _prepareForm()
    {
        $model = $this->_coreRegistry->registry('current_promo_quote_rule');

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('rule_');

        $fieldset = $form->addFieldset(
            'action_fieldset',
            array('legend' => __('Update prices using the following information'))
        );

        $fieldset->addField(
            'simple_action',
            'select',
            array(
                'label' => __('Apply'),
                'name' => 'simple_action',
                'options' => array(
                    \Magento\SalesRule\Model\Rule::BY_PERCENT_ACTION => __('Percent of product price discount'),
                    \Magento\SalesRule\Model\Rule::BY_FIXED_ACTION => __('Fixed amount discount'),
                    \Magento\SalesRule\Model\Rule::CART_FIXED_ACTION => __('Fixed amount discount for whole cart'),
                    \Magento\SalesRule\Model\Rule::BUY_X_GET_Y_ACTION => __('Buy X get Y free (discount amount is Y)')
                )
            )
        );
        $fieldset->addField(
            'discount_amount',
            'text',
            array(
                'name' => 'discount_amount',
                'required' => true,
                'class' => 'validate-not-negative-number',
                'label' => __('Discount Amount')
            )
        );
        $model->setDiscountAmount($model->getDiscountAmount() * 1);

        $fieldset->addField(
            'discount_qty',
            'text',
            array('name' => 'discount_qty', 'label' => __('Maximum Qty Discount is Applied To'))
        );
        $model->setDiscountQty($model->getDiscountQty() * 1);

        $fieldset->addField(
            'discount_step',
            'text',
            array('name' => 'discount_step', 'label' => __('Discount Qty Step (Buy X)'))
        );

        $fieldset->addField(
            'apply_to_shipping',
            'select',
            array(
                'label' => __('Apply to Shipping Amount'),
                'title' => __('Apply to Shipping Amount'),
                'name' => 'apply_to_shipping',
                'values' => $this->_sourceYesno->toOptionArray()
            )
        );

        $fieldset->addField(
            'stop_rules_processing',
            'select',
            array(
                'label' => __('Stop Further Rules Processing'),
                'title' => __('Stop Further Rules Processing'),
                'name' => 'stop_rules_processing',
                'options' => array('1' => __('Yes'), '0' => __('No'))
            )
        );

        $renderer = $this->_rendererFieldset->setTemplate(
            'Magento_CatalogRule::promo/fieldset.phtml'
        )->setNewChildUrl(
            $this->getUrl('sales_rule/promo_quote/newActionHtml/form/rule_actions_fieldset')
        );

        $fieldset = $form->addFieldset(
            'actions_fieldset',
            array(
                'legend' => __(
                    'Apply the rule only to cart items matching the following conditions ' .
                    '(leave blank for all items).'
                )
            )
        )->setRenderer(
            $renderer
        );

        $fieldset->addField(
            'actions',
            'text',
            array('name' => 'actions', 'label' => __('Apply To'), 'title' => __('Apply To'), 'required' => true)
        )->setRule(
            $model
        )->setRenderer(
            $this->_ruleActions
        );

        $this->_eventManager->dispatch('adminhtml_block_salesrule_actions_prepareform', array('form' => $form));

        $form->setValues($model->getData());

        if ($model->isReadonly()) {
            foreach ($fieldset->getElements() as $element) {
                $element->setReadonly(true, true);
            }
        }

        $this->setForm($form);
        return parent::_prepareForm();
    }
}
