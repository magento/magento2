<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Block\Adminhtml\Order\Status\Assign;

/**
 * Assign order status to order state form
 */
class Form extends \Magento\Backend\Block\Widget\Form\Generic
{
    /**
     * Collection factory
     *
     * @var \Magento\Sales\Model\ResourceModel\Order\Status\CollectionFactory
     */
    protected $_collectionFactory;

    /**
     * Order config
     *
     * @var \Magento\Sales\Model\Order\Config
     */
    protected $_orderConfig;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Sales\Model\Order\Config $orderConfig
     * @param \Magento\Sales\Model\ResourceModel\Order\Status\CollectionFactory $collectionFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Sales\Model\Order\Config $orderConfig,
        \Magento\Sales\Model\ResourceModel\Order\Status\CollectionFactory $collectionFactory,
        array $data = []
    ) {
        $this->_orderConfig = $orderConfig;
        $this->_collectionFactory = $collectionFactory;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('order_status_state');
    }

    /**
     * Prepare form fields
     *
     * @return $this
     */
    protected function _prepareForm()
    {
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create(['data' => ['id' => 'edit_form', 'method' => 'post']]);

        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Assignment Information')]);

        $statuses = $this->_collectionFactory->create()->toOptionArray();
        array_unshift($statuses, ['value' => '', 'label' => '']);

        $states = $this->_orderConfig->getStates();
        $states = array_merge(['' => ''], $states);

        $fieldset->addField(
            'status',
            'select',
            [
                'name' => 'status',
                'label' => __('Order Status'),
                'class' => 'required-entry',
                'values' => $statuses,
                'required' => true
            ]
        );

        $fieldset->addField(
            'state',
            'select',
            [
                'name' => 'state',
                'label' => __('Order State'),
                'class' => 'required-entry',
                'values' => $states,
                'required' => true
            ]
        );

        $fieldset->addField(
            'is_default',
            'checkbox',
            ['name' => 'is_default', 'label' => __('Use Order Status As Default'), 'value' => 1]
        );

        $fieldset->addField(
            'visible_on_front',
            'checkbox',
            ['name' => 'visible_on_front', 'label' => __('Visible On Storefront'), 'value' => 1]
        );

        $form->setAction($this->getUrl('sales/order_status/assignPost'));
        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }
}
