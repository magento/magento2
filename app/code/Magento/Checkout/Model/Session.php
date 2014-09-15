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
namespace Magento\Checkout\Model;

use Magento\Customer\Service\V1\Data\Customer as CustomerDataObject;
use Magento\Customer\Service\V1\Data\CustomerBuilder;
use Magento\Sales\Model\Quote;

class Session extends \Magento\Framework\Session\SessionManager
{
    /**
     * Checkout state begin
     */
    const CHECKOUT_STATE_BEGIN = 'begin';

    /**
     * Quote instance
     *
     * @var Quote
     */
    protected $_quote;

    /**
     * Customer Data Object
     *
     * @var null|CustomerDataObject
     */
    protected $_customer;

    /**
     * Whether load only active quote
     *
     * @var bool
     */
    protected $_loadInactive = false;

    /**
     * Loaded order instance
     *
     * @var \Magento\Sales\Model\Order
     */
    protected $_order;

    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $_orderFactory;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var \Magento\Sales\Model\QuoteFactory
     */
    protected $_quoteFactory;

    /**
     * @var \Magento\Framework\HTTP\PhpEnvironment\RemoteAddress
     */
    protected $_remoteAddress;

    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $_eventManager;

    /**
     * @var \Magento\Framework\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @param \Magento\Framework\App\Request\Http $request
     * @param \Magento\Framework\Session\SidResolverInterface $sidResolver
     * @param \Magento\Framework\Session\Config\ConfigInterface $sessionConfig
     * @param \Magento\Framework\Session\SaveHandlerInterface $saveHandler
     * @param \Magento\Framework\Session\ValidatorInterface $validator
     * @param \Magento\Framework\Session\StorageInterface $storage
     * @param \Magento\Framework\Stdlib\CookieManager $cookieManager
     * @param \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory $cookieMetadataFactory
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Sales\Model\QuoteFactory $quoteFactory
     * @param \Magento\Framework\HTTP\PhpEnvironment\RemoteAddress $remoteAddress
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Framework\StoreManagerInterface $storeManager
     * @param null $sessionName
     */
    public function __construct(
        \Magento\Framework\App\Request\Http $request,
        \Magento\Framework\Session\SidResolverInterface $sidResolver,
        \Magento\Framework\Session\Config\ConfigInterface $sessionConfig,
        \Magento\Framework\Session\SaveHandlerInterface $saveHandler,
        \Magento\Framework\Session\ValidatorInterface $validator,
        \Magento\Framework\Session\StorageInterface $storage,
        \Magento\Framework\Stdlib\CookieManager $cookieManager,
        \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory $cookieMetadataFactory,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Sales\Model\QuoteFactory $quoteFactory,
        \Magento\Framework\HTTP\PhpEnvironment\RemoteAddress $remoteAddress,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Framework\StoreManagerInterface $storeManager
    ) {
        $this->_orderFactory = $orderFactory;
        $this->_customerSession = $customerSession;
        $this->_quoteFactory = $quoteFactory;
        $this->_remoteAddress = $remoteAddress;
        $this->_eventManager = $eventManager;
        $this->_storeManager = $storeManager;
        parent::__construct(
            $request,
            $sidResolver,
            $sessionConfig,
            $saveHandler,
            $validator,
            $storage,
            $cookieManager,
            $cookieMetadataFactory
        );
        $this->start();
    }

    /**
     * Set customer data.
     *
     * @param CustomerDataObject|null $customer
     * @return \Magento\Checkout\Model\Session
     */
    public function setCustomerData($customer)
    {
        $this->_customer = $customer;
        return $this;
    }

    /**
     * Check whether current session has quote
     *
     * @return bool
     */
    public function hasQuote()
    {
        return isset($this->_quote);
    }

    /**
     * Set quote to be loaded even if inactive
     *
     * @param bool $load
     * @return $this
     */
    public function setLoadInactive($load = true)
    {
        $this->_loadInactive = $load;
        return $this;
    }

    /**
     * Get checkout quote instance by current session
     *
     * @return Quote
     */
    public function getQuote()
    {
        $this->_eventManager->dispatch('custom_quote_process', array('checkout_session' => $this));

        if ($this->_quote === null) {
            /** @var $quote Quote */
            $quote = $this->_quoteFactory->create()->setStoreId($this->_storeManager->getStore()->getId());
            if ($this->getQuoteId()) {
                if ($this->_loadInactive) {
                    $quote->load($this->getQuoteId());
                } else {
                    $quote->loadActive($this->getQuoteId());
                }
                if ($quote->getId()) {
                    /**
                     * If current currency code of quote is not equal current currency code of store,
                     * need recalculate totals of quote. It is possible if customer use currency switcher or
                     * store switcher.
                     */
                    if ($quote->getQuoteCurrencyCode() != $this->_storeManager->getStore()->getCurrentCurrencyCode()) {
                        $quote->setStore($this->_storeManager->getStore());
                        $quote->collectTotals()->save();
                        /*
                         * We mast to create new quote object, because collectTotals()
                         * can to create links with other objects.
                         */
                        $quote = $this->_quoteFactory->create()->setStoreId($this->_storeManager->getStore()->getId());
                        $quote->load($this->getQuoteId());
                    }
                } else {
                    $this->setQuoteId(null);
                }
            }

            if (!$this->getQuoteId()) {
                if ($this->_customerSession->isLoggedIn() || $this->_customer) {
                    $customerId = $this->_customer ? $this
                        ->_customer
                        ->getId() : $this
                        ->_customerSession
                        ->getCustomerId();
                    $quote->loadByCustomer($customerId);
                    $this->setQuoteId($quote->getId());
                } else {
                    $quote->setIsCheckoutCart(true);
                    $this->_eventManager->dispatch('checkout_quote_init', array('quote' => $quote));
                }
            }

            if ($this->getQuoteId()) {
                if ($this->_customer) {
                    $quote->setCustomerData($this->_customer);
                } else if ($this->_customerSession->isLoggedIn()) {
                    $quote->setCustomerData($this->_customerSession->getCustomerDataObject());
                }
            }

            $quote->setStore($this->_storeManager->getStore());
            $this->_quote = $quote;
        }

        if ($remoteAddr = $this->_remoteAddress->getRemoteAddress()) {
            $this->_quote->setRemoteIp($remoteAddr);
            $xForwardIp = $this->request->getServer('HTTP_X_FORWARDED_FOR');
            $this->_quote->setXForwardedFor($xForwardIp);
        }
        return $this->_quote;
    }

    /**
     * @return string
     */
    protected function _getQuoteIdKey()
    {
        return 'quote_id_' . $this->_storeManager->getStore()->getWebsiteId();
    }

    /**
     * @param int $quoteId
     * @return void
     */
    public function setQuoteId($quoteId)
    {
        $this->storage->setData($this->_getQuoteIdKey(), $quoteId);
    }

    /**
     * @return int
     */
    public function getQuoteId()
    {
        return $this->getData($this->_getQuoteIdKey());
    }

    /**
     * Load data for customer quote and merge with current quote
     *
     * @return $this
     */
    public function loadCustomerQuote()
    {
        if (!$this->_customerSession->getCustomerId()) {
            return $this;
        }

        $this->_eventManager->dispatch('load_customer_quote_before', array('checkout_session' => $this));

        $customerQuote = $this->_quoteFactory->create()->setStoreId(
            $this->_storeManager->getStore()->getId()
        )->loadByCustomer(
            $this->_customerSession->getCustomerId()
        );

        if ($customerQuote->getId() && $this->getQuoteId() != $customerQuote->getId()) {
            if ($this->getQuoteId()) {
                $customerQuote->merge($this->getQuote())->collectTotals()->save();
            }

            $this->setQuoteId($customerQuote->getId());

            if ($this->_quote) {
                $this->_quote->delete();
            }
            $this->_quote = $customerQuote;
        } else {
            $this->getQuote()->getBillingAddress();
            $this->getQuote()->getShippingAddress();
            $this->getQuote()->setCustomerData(
                $this->_customerSession->getCustomerDataObject()
            )->setTotalsCollectedFlag(
                false
            )->collectTotals()->save();
        }
        return $this;
    }

    /**
     * @param string $step
     * @param array|string $data
     * @param bool|string|null $value
     * @return $this
     */
    public function setStepData($step, $data, $value = null)
    {
        $steps = $this->getSteps();
        if (is_null($value)) {
            if (is_array($data)) {
                $steps[$step] = $data;
            }
        } else {
            if (!isset($steps[$step])) {
                $steps[$step] = array();
            }
            if (is_string($data)) {
                $steps[$step][$data] = $value;
            }
        }
        $this->setSteps($steps);

        return $this;
    }

    /**
     * @param string|null $step
     * @param string|null $data
     * @return array|string|bool
     */
    public function getStepData($step = null, $data = null)
    {
        $steps = $this->getSteps();
        if (is_null($step)) {
            return $steps;
        }
        if (!isset($steps[$step])) {
            return false;
        }
        if (is_null($data)) {
            return $steps[$step];
        }
        if (!is_string($data) || !isset($steps[$step][$data])) {
            return false;
        }
        return $steps[$step][$data];
    }

    /**
     * Destroy/end a session
     * Unset all data associated with object
     *
     * @return $this
     */
    public function clearQuote()
    {
        $this->_eventManager->dispatch('checkout_quote_destroy', array('quote' => $this->getQuote()));
        $this->_quote = null;
        $this->setQuoteId(null);
        $this->setLastSuccessQuoteId(null);
        return $this;
    }

    /**
     * Unset all session data and quote
     *
     * @return $this
     */
    public function clearStorage()
    {
        parent::clearStorage();
        $this->_quote = null;
        return $this;
    }

    /**
     * Clear misc checkout parameters
     *
     * @return void
     */
    public function clearHelperData()
    {
        $this->setRedirectUrl(null)->setLastOrderId(null)->setLastRealOrderId(null)->setAdditionalMessages(null);
    }

    /**
     * @return $this
     */
    public function resetCheckout()
    {
        $this->setCheckoutState(self::CHECKOUT_STATE_BEGIN);
        return $this;
    }

    /**
     * @param Quote $quote
     * @return $this
     */
    public function replaceQuote($quote)
    {
        $this->_quote = $quote;
        $this->setQuoteId($quote->getId());
        return $this;
    }

    /**
     * Get order instance based on last order ID
     *
     * @return \Magento\Sales\Model\Order
     */
    public function getLastRealOrder()
    {
        $orderId = $this->getLastRealOrderId();
        if ($this->_order !== null && $orderId == $this->_order->getIncrementId()) {
            return $this->_order;
        }
        $this->_order = $this->_orderFactory->create();
        if ($orderId) {
            $this->_order->loadByIncrementId($orderId);
        }
        return $this->_order;
    }

    /**
     * Restore last active quote
     *
     * @return bool True if quote restored successfully, false otherwise
     */
    public function restoreQuote()
    {
        /** @var \Magento\Sales\Model\Order $order */
        $order = $this->getLastRealOrder();
        if ($order->getId()) {
            /** @var \Magento\Sales\Model\Quote $quote */
            $quote = $this->_quoteFactory->create()->load($order->getQuoteId());
            if ($quote->getId()) {
                $quote->setIsActive(1)->setReservedOrderId(null)->save();
                $this->replaceQuote($quote)->unsLastRealOrderId();
                $this->_eventManager->dispatch('restore_quote', array('order' => $order, 'quote' => $quote));
                return true;
            }
        }
        return false;
    }
}
