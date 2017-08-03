<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Block\Adminhtml\Report\Filter;

/**
 * Sales Adminhtml report filter form
 *
 * @api
 * @author     Magento Core Team <core@magentocommerce.com>
 * @SuppressWarnings(PHPMD.DepthOfInheritance)
 */
class Form extends \Magento\Reports\Block\Adminhtml\Filter\Form
{
    /**
     * Order config
     *
     * @var \Magento\Sales\Model\Order\ConfigFactory
     */
    protected $_orderConfig;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Sales\Model\Order\ConfigFactory $orderConfig
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Sales\Model\Order\ConfigFactory $orderConfig,
        array $data = []
    ) {
        $this->_orderConfig = $orderConfig;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Add fields to base fieldset which are general to sales reports
     *
     * @return $this
     */
    protected function _prepareForm()
    {
        parent::_prepareForm();
        $form = $this->getForm();
        $htmlIdPrefix = $form->getHtmlIdPrefix();
        /** @var \Magento\Framework\Data\Form\Element\Fieldset $fieldset */
        $fieldset = $this->getForm()->getElement('base_fieldset');

        if (is_object($fieldset) && $fieldset instanceof \Magento\Framework\Data\Form\Element\Fieldset) {
            $statuses = $this->_orderConfig->create()->getStatuses();
            $values = [];
            foreach ($statuses as $code => $label) {
                if (false === strpos($code, 'pending')) {
                    $values[] = ['label' => __($label), 'value' => $code];
                }
            }

            $fieldset->addField(
                'show_order_statuses',
                'select',
                [
                    'name' => 'show_order_statuses',
                    'label' => __('Order Status'),
                    'options' => ['0' => __('Any'), '1' => __('Specified')],
                    'note' => __('Applies to Any of the Specified Order Statuses except canceled orders')
                ],
                'to'
            );

            $fieldset->addField(
                'order_statuses',
                'multiselect',
                [
                    'name' => 'order_statuses',
                    'label' => '',
                    'values' => $values,
                    'display' => 'none'
                ],
                'show_order_statuses'
            );

            // define field dependencies
            if ($this->getFieldVisibility('show_order_statuses') && $this->getFieldVisibility('order_statuses')) {
                $this->setChild(
                    'form_after',
                    $this->getLayout()->createBlock(
                        \Magento\Backend\Block\Widget\Form\Element\Dependence::class
                    )->addFieldMap(
                        "{$htmlIdPrefix}show_order_statuses",
                        'show_order_statuses'
                    )->addFieldMap(
                        "{$htmlIdPrefix}order_statuses",
                        'order_statuses'
                    )->addFieldDependence(
                        'order_statuses',
                        'show_order_statuses',
                        '1'
                    )
                );
            }
        }

        return $this;
    }
}
