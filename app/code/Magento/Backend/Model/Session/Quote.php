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
namespace Magento\Backend\Model\Session;

/**
 * Adminhtml quote session
 *
 * @method Quote setCustomerId($id)
 * @method int getCustomerId()
 * @method bool hasCustomerId()
 * @method Quote setStoreId($storeId)
 * @method int getStoreId()
 * @method Quote setQuoteId($quoteId)
 * @method int getQuoteId()
 * @method Quote setCurrencyId($currencyId)
 * @method int getCurrencyId()
 * @method Quote setOrderId($orderId)
 * @method int getOrderId()
 */
class Quote extends \Magento\Framework\Session\SessionManager
{
    const XML_PATH_DEFAULT_CREATEACCOUNT_GROUP = 'customer/create_account/default_group';

    /**
     * Quote model object
     *
     * @var \Magento\Sales\Model\Quote
     */
    protected $_quote = null;

    /**
     * Store model object
     *
     * @var \Magento\Store\Model\Store
     */
    protected $_store = null;

    /**
     * Order model object
     *
     * @var \Magento\Sales\Model\Order
     */
    protected $_order = null;

    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $_orderFactory;

    /**
     * @var \Magento\Customer\Service\V1\CustomerAccountServiceInterface
     */
    protected $_customerService;

    /**
     * @var \Magento\Sales\Model\QuoteFactory
     */
    protected $_quoteFactory;

    /**
     * @var \Magento\Framework\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @param \Magento\Framework\App\Request\Http $request
     * @param \Magento\Framework\Session\SidResolverInterface $sidResolver
     * @param \Magento\Framework\Session\Config\ConfigInterface $sessionConfig
     * @param \Magento\Framework\Session\SaveHandlerInterface $saveHandler
     * @param \Magento\Framework\Session\ValidatorInterface $validator
     * @param \Magento\Framework\Session\StorageInterface $storage
     * @param \Magento\Framework\Stdlib\CookieManager $cookieManager
     * @param \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory $cookieMetadataFactory
     * @param \Magento\Sales\Model\QuoteFactory $quoteFactory
     * @param \Magento\Customer\Service\V1\CustomerAccountServiceInterface $customerService
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     * @param \Magento\Framework\StoreManagerInterface $storeManager
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
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
        \Magento\Sales\Model\QuoteFactory $quoteFactory,
        \Magento\Customer\Service\V1\CustomerAccountServiceInterface $customerService,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Framework\StoreManagerInterface $storeManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->_quoteFactory = $quoteFactory;
        $this->_customerService = $customerService;
        $this->_orderFactory = $orderFactory;
        $this->_storeManager = $storeManager;
        $this->_scopeConfig = $scopeConfig;
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
        if ($this->_storeManager->hasSingleStore()) {
            $this->setStoreId($this->_storeManager->getStore(true)->getId());
        }
    }

    /**
     * Retrieve quote model object
     *
     * @return \Magento\Sales\Model\Quote
     */
    public function getQuote()
    {
        if (is_null($this->_quote)) {
            $this->_quote = $this->_quoteFactory->create();
            if ($this->getStoreId() && $this->getQuoteId()) {
                $this->_quote->setStoreId($this->getStoreId())->load($this->getQuoteId());
            } elseif ($this->getStoreId() && $this->hasCustomerId()) {
                $customerGroupId = $this->_scopeConfig->getValue(
                    self::XML_PATH_DEFAULT_CREATEACCOUNT_GROUP,
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                );
                $this->_quote
                    ->setStoreId($this->getStoreId())
                    ->setCustomerGroupId($customerGroupId)
                    ->setIsActive(false)
                    ->save();
                $this->setQuoteId($this->_quote->getId());
                try {
                    $customerData = $this->_customerService->getCustomer($this->getCustomerId());
                    $this->_quote->assignCustomer($customerData);
                } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
                    /** Customer does not exist */
                }
            }
            $this->_quote->setIgnoreOldQty(true);
            $this->_quote->setIsSuperMode(true);
        }
        return $this->_quote;
    }

    /**
     * Retrieve store model object
     *
     * @return \Magento\Store\Model\Store
     */
    public function getStore()
    {
        if (is_null($this->_store)) {
            $this->_store = $this->_storeManager->getStore($this->getStoreId());
            $currencyId = $this->getCurrencyId();
            if ($currencyId) {
                $this->_store->setCurrentCurrencyCode($currencyId);
            }
        }
        return $this->_store;
    }

    /**
     * Retrieve order model object
     *
     * @return \Magento\Sales\Model\Order
     */
    public function getOrder()
    {
        if (is_null($this->_order)) {
            $this->_order = $this->_orderFactory->create();
            if ($this->getOrderId()) {
                $this->_order->load($this->getOrderId());
            }
        }
        return $this->_order;
    }
}
