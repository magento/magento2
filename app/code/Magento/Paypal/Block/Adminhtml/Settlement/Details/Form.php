<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Block\Adminhtml\Settlement\Details;

/**
 * Settlement reports transaction details
 * @since 2.0.0
 */
class Form extends \Magento\Backend\Block\Widget\Form\Generic
{
    /**
     * @var \Magento\Paypal\Model\Report\Settlement
     * @since 2.0.0
     */
    protected $_settlement;

    /**
     * @var \Magento\Framework\Locale\CurrencyInterface
     * @since 2.0.0
     */
    protected $_localeCurrency;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Paypal\Model\Report\Settlement $settlement
     * @param \Magento\Framework\Locale\CurrencyInterface $localeCurrency
     * @param array $data
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Paypal\Model\Report\Settlement $settlement,
        \Magento\Framework\Locale\CurrencyInterface $localeCurrency,
        array $data = []
    ) {
        $this->_settlement = $settlement;
        $this->_localeCurrency = $localeCurrency;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Prepare read-only data and group it by fieldsets
     *
     * @return $this
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @since 2.0.0
     */
    protected function _prepareForm()
    {
        /* @var $model \Magento\Paypal\Model\Report\Settlement\Row */
        $model = $this->_coreRegistry->registry('current_transaction');
        $fieldsets = [
            'reference_fieldset' => [
                'fields' => [
                    'transaction_id' => ['label' => $this->_settlement->getFieldLabel('transaction_id')],
                    'invoice_id' => ['label' => $this->_settlement->getFieldLabel('invoice_id')],
                    'paypal_reference_id' => [
                        'label' => $this->_settlement->getFieldLabel('paypal_reference_id'),
                    ],
                    'paypal_reference_id_type' => [
                        'label' => $this->_settlement->getFieldLabel('paypal_reference_id_type'),
                        'value' => $model->getReferenceType($model->getData('paypal_reference_id_type')),
                    ],
                    'custom_field' => ['label' => $this->_settlement->getFieldLabel('custom_field')],
                ],
                'legend' => __('Reference Information'),
            ],
            'transaction_fieldset' => [
                'fields' => [
                    'transaction_event_code' => [
                        'label' => $this->_settlement->getFieldLabel('transaction_event_code'),
                        'value' => sprintf(
                            '%s (%s)',
                            $model->getData('transaction_event_code'),
                            $model->getTransactionEvent($model->getData('transaction_event_code'))
                        ),
                    ],
                    'transaction_initiation_date' => [
                        'label' => $this->_settlement->getFieldLabel('transaction_initiation_date'),
                        'value' => $this->formatDate(
                            $model->getData('transaction_initiation_date'),
                            \IntlDateFormatter::MEDIUM,
                            true
                        ),
                    ],
                    'transaction_completion_date' => [
                        'label' => $this->_settlement->getFieldLabel('transaction_completion_date'),
                        'value' => $this->formatDate(
                            $model->getData('transaction_completion_date'),
                            \IntlDateFormatter::MEDIUM,
                            true
                        ),
                    ],
                    'transaction_debit_or_credit' => [
                        'label' => $this->_settlement->getFieldLabel('transaction_debit_or_credit'),
                        'value' => $model->getDebitCreditText($model->getData('transaction_debit_or_credit')),
                    ],
                    'gross_transaction_amount' => [
                        'label' => $this->_settlement->getFieldLabel('gross_transaction_amount'),
                        'value' => $this->_localeCurrency->getCurrency(
                            $model->getData('gross_transaction_currency')
                        )->toCurrency(
                            $model->getData('gross_transaction_amount')
                        ),
                    ],
                ],
                'legend' => __('Transaction Information'),
            ],
            'fee_fieldset' => [
                'fields' => [
                    'fee_debit_or_credit' => [
                        'label' => $this->_settlement->getFieldLabel('fee_debit_or_credit'),
                        'value' => $model->getDebitCreditText($model->getCastedAmount('fee_debit_or_credit')),
                    ],
                    'fee_amount' => [
                        'label' => $this->_settlement->getFieldLabel('fee_amount'),
                        'value' => $this->_localeCurrency->getCurrency(
                            $model->getData('fee_currency')
                        )->toCurrency(
                            $model->getCastedAmount('fee_amount')
                        ),
                    ],
                ],
                'legend' => __('PayPal Fee Information'),
            ],
        ];

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();
        foreach ($fieldsets as $key => $data) {
            $fieldset = $form->addFieldset($key, ['legend' => $data['legend']]);
            foreach ($data['fields'] as $id => $info) {
                $fieldset->addField(
                    $id,
                    'label',
                    [
                        'name' => $id,
                        'label' => $info['label'],
                        'title' => $info['label'],
                        'value' => isset($info['value']) ? $info['value'] : $model->getData($id)
                    ]
                );
            }
        }
        $this->setForm($form);
        return parent::_prepareForm();
    }
}
