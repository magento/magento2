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
 * @category    Magento
 * @package     Magento_Checkout
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Checkout\Model\Type;

use Magento\Customer\Service\V1\CustomerAddressServiceInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Quote\Item;

/**
 * Checkout type abstract class
 *
 * @category   Magento
 * @package    Magento_Checkout
 * @author      Magento Core Team <core@magentocommerce.com>
 */
abstract class AbstractType extends \Magento\Object
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
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $_orderFactory;

    /**
     * @var CustomerAddressServiceInterface
     */
    protected $_customerAddressService;

    /**
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     * @param CustomerAddressServiceInterface $customerAddressService
     * @param array $data
     */
    public function __construct(
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        CustomerAddressServiceInterface $customerAddressService,
        array $data = array()
    ) {
        parent::__construct($data);
        $this->_checkoutSession = $checkoutSession;
        $this->_customerSession = $customerSession;
        $this->_orderFactory = $orderFactory;
        $this->_customerAddressService = $customerAddressService;
    }

    /**
     * Retrieve checkout session model
     *
     * @return \Magento\Checkout\Model\Session
     */
    public function getCheckoutSession()
    {
        $checkout = $this->getData('checkout_session');
        if (is_null($checkout)) {
            $checkout = $this->_checkoutSession;
            $this->setData('checkout_session', $checkout);
        }
        return $checkout;
    }

    /**
     * Retrieve quote model
     *
     * @return \Magento\Sales\Model\Quote
     */
    public function getQuote()
    {
        return $this->getCheckoutSession()->getQuote();
    }

    /**
     * Retrieve quote items
     *
     * @return Item[]
     */
    public function getQuoteItems()
    {
        return $this->getQuote()->getAllItems();
    }

    /**
     * Retrieve customer session model
     *
     * @return \Magento\Customer\Model\Session
     */
    public function getCustomerSession()
    {
        return $this->_customerSession;
    }

    /**
     * Retrieve customer object
     *
     * @return \Magento\Customer\Service\V1\Dto\Customer
     */
    public function getCustomer()
    {
        return $this->_customerSession->getCustomerData();
    }

    /**
     * Retrieve customer default shipping address
     *
     * @return \Magento\Customer\Service\V1\Dto\Address|null
     */
    public function getCustomerDefaultShippingAddress()
    {
        $address = $this->getData('customer_default_shipping_address');
        if (is_null($address)) {
            $customerId = $this->getCustomer()->getCustomerId();
            $address = $this->_customerAddressService->getDefaultShippingAddress($customerId);
            if (!$address) {
                /** Default shipping address is not available, try to find any customer address */
                $allAddresses = $this->_customerAddressService->getAddresses($customerId);
                $address = count($allAddresses) ? reset($allAddresses) : null;
            }
            $this->setData('customer_default_shipping_address', $address);
        }
        return $address;
    }

    /**
     * Retrieve customer default billing address
     *
     * @return \Magento\Customer\Service\V1\Dto\Address|null
     */
    public function getCustomerDefaultBillingAddress()
    {
        $address = $this->getData('customer_default_billing_address');
        if (is_null($address)) {
            $customerId = $this->getCustomer()->getCustomerId();
            $address = $this->_customerAddressService->getDefaultBillingAddress($customerId);
            if (!$address) {
                /** Default billing address is not available, try to find any customer address */
                $allAddresses = $this->_customerAddressService->getAddresses($customerId);
                $address = count($allAddresses) ? reset($allAddresses) : null;
            }
            $this->setData('customer_default_billing_address', $address);
        }
        return $address;
    }
}
