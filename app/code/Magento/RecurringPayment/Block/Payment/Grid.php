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
namespace Magento\RecurringPayment\Block\Payment;

use Magento\Customer\Controller\RegistryConstants;

/**
 * Recurring payment view grid
 */
class Grid extends \Magento\RecurringPayment\Block\Payments
{
    /**
     * @var \Magento\Framework\Registry
     */
    protected $_registry;

    /**
     * @var \Magento\RecurringPayment\Model\Payment
     */
    protected $_recurringPayment;

    /**
     * Payments collection
     *
     * @var \Magento\RecurringPayment\Model\Resource\Payment\Collection
     */
    protected $_payments = null;

    /**
     * @var \Magento\RecurringPayment\Block\Fields
     */
    protected $_fields;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\RecurringPayment\Model\Payment $recurringPayment
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\RecurringPayment\Block\Fields $fields
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\RecurringPayment\Model\Payment $recurringPayment,
        \Magento\Framework\Registry $registry,
        \Magento\RecurringPayment\Block\Fields $fields,
        array $data = array()
    ) {
        $this->_recurringPayment = $recurringPayment;
        $this->_registry = $registry;
        parent::__construct($context, $data);
        $this->_fields = $fields;
        $this->_isScopePrivate = true;
    }

    /**
     * Instantiate payments collection
     *
     * @param array|int|string $fields
     * @return void
     */
    protected function _preparePayments($fields = '*')
    {
        $this->_payments = $this->_recurringPayment->getCollection()->addFieldToFilter(
            'customer_id',
            $this->_registry->registry(RegistryConstants::CURRENT_CUSTOMER_ID)
        )->addFieldToSelect(
            $fields
        )->setOrder(
            'payment_id',
            'desc'
        );
    }

    /**
     * Prepare grid data
     *
     * @return void
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        $this->_preparePayments(array('reference_id', 'state', 'created_at', 'updated_at', 'method_code'));

        $pager = $this->getLayout()->createBlock(
            'Magento\Theme\Block\Html\Pager'
        )->setCollection(
            $this->_payments
        )->setIsOutputRequired(
            false
        );
        $this->setChild('pager', $pager);

        $this->setGridColumns(
            array(
                new \Magento\Framework\Object(
                    array(
                        'index' => 'reference_id',
                        'title' => $this->_fields->getFieldLabel('reference_id'),
                        'is_nobr' => true,
                        'width' => 1
                    )
                ),
                new \Magento\Framework\Object(
                    array(
                        'index' => 'state',
                        'title' => $this->_fields->getFieldLabel('state')
                    )
                ),
                new \Magento\Framework\Object(
                    array(
                        'index' => 'created_at',
                        'title' => $this->_fields->getFieldLabel('created_at'),
                        'is_nobr' => true,
                        'width' => 1,
                        'is_amount' => true
                    )
                ),
                new \Magento\Framework\Object(
                    array(
                        'index' => 'updated_at',
                        'title' => $this->_fields->getFieldLabel('updated_at'),
                        'is_nobr' => true,
                        'width' => 1
                    )
                ),
                new \Magento\Framework\Object(
                    array(
                        'index' => 'method_code',
                        'title' => $this->_fields->getFieldLabel('method_code'),
                        'is_nobr' => true,
                        'width' => 1
                    )
                )
            )
        );

        $payments = array();
        $store = $this->_storeManager->getStore();
        foreach ($this->_payments as $payment) {
            $payment->setStore($store);
            $payments[] = new \Magento\Framework\Object(
                array(
                    'reference_id' => $payment->getReferenceId(),
                    'reference_id_link_url' => $this->getUrl(
                        'sales/recurringPayment/view/',
                        array('payment' => $payment->getId())
                    ),
                    'state' => $payment->renderData('state'),
                    'created_at' => $this->formatDate($payment->getData('created_at'), 'medium', true),
                    'updated_at' => $payment->getData(
                        'updated_at'
                    ) ? $this->formatDate(
                        $payment->getData('updated_at'),
                        'short',
                        true
                    ) : '',
                    'method_code' => $payment->renderData('method_code')
                )
            );
        }
        if ($payments) {
            $this->setGridElements($payments);
        }
    }
}
