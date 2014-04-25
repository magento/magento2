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
namespace Magento\RecurringPayment\Block\Checkout\Onepage;

/**
 * Recurring Payment information on Order success page
 */
class Success extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var \Magento\RecurringPayment\Model\Resource\Payment\CollectionFactory
     */
    protected $_recurringPaymentCollectionFactory;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\RecurringPayment\Model\Resource\Payment\CollectionFactory $recurringPaymentCollectionFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\RecurringPayment\Model\Resource\Payment\CollectionFactory $recurringPaymentCollectionFactory,
        array $data = array()
    ) {
        $this->_checkoutSession = $checkoutSession;
        $this->_customerSession = $customerSession;
        $this->_recurringPaymentCollectionFactory = $recurringPaymentCollectionFactory;
        parent::__construct($context, $data);
    }

    /**
     * Getter for recurring payment view page
     *
     * @param \Magento\Framework\Object $payment
     * @return string
     */
    public function getPaymentUrl(\Magento\Framework\Object $payment)
    {
        return $this->getUrl('sales/recurringPayment/view', array('payment' => $payment->getId()));
    }

    /**
     * Before rendering html, but after trying to load cache
     *
     * @return $this
     */
    protected function _beforeToHtml()
    {
        $this->_prepareLastRecurringPayments();
        return parent::_beforeToHtml();
    }

    /**
     * Prepare recurring payments from the session
     *
     * @return void
     */
    protected function _prepareLastRecurringPayments()
    {
        $paymentIds = $this->_checkoutSession->getLastRecurringPaymentIds();
        if ($paymentIds && is_array($paymentIds)) {
            $collection = $this->_recurringPaymentCollectionFactory->create()->addFieldToFilter(
                'payment_id',
                array('in' => $paymentIds)
            );
            $payments = array();
            foreach ($collection as $payment) {
                $payments[] = $payment;
            }
            if ($payments) {
                $this->setRecurringPayments($payments);
                if ($this->_customerSession->isLoggedIn()) {
                    $this->setCanViewPayments(true);
                }
            }
        }
    }
}
