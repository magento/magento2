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

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\GroupManagementInterface;

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
    /**
     * Quote model object
     *
     * @var \Magento\Sales\Model\Quote
     */
    protected $_quote;

    /**
     * Store model object
     *
     * @var \Magento\Store\Model\Store
     */
    protected $_store;

    /**
     * Order model object
     *
     * @var \Magento\Sales\Model\Order
     */
    protected $_order;

    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $_orderFactory;

    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * Sales quote repository
     *
     * @var \Magento\Sales\Model\QuoteRepository
     */
    protected $quoteRepository;

    /**
     * @var \Magento\Framework\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var GroupManagementInterface
     */
    protected $groupManagement;

    /**
     * Constructor
     *
     * @param \Magento\Framework\App\Request\Http $request
     * @param \Magento\Framework\Session\SidResolverInterface $sidResolver
     * @param \Magento\Framework\Session\Config\ConfigInterface $sessionConfig
     * @param \Magento\Framework\Session\SaveHandlerInterface $saveHandler
     * @param \Magento\Framework\Session\ValidatorInterface $validator
     * @param \Magento\Framework\Session\StorageInterface $storage
     * @param \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager
     * @param \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory $cookieMetadataFactory
     * @param \Magento\Sales\Model\QuoteRepository $quoteRepository
     * @param CustomerRepositoryInterface $customerRepository
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     * @param \Magento\Framework\StoreManagerInterface $storeManager
     * @param GroupManagementInterface $groupManagement
     */
    public function __construct(
        \Magento\Framework\App\Request\Http $request,
        \Magento\Framework\Session\SidResolverInterface $sidResolver,
        \Magento\Framework\Session\Config\ConfigInterface $sessionConfig,
        \Magento\Framework\Session\SaveHandlerInterface $saveHandler,
        \Magento\Framework\Session\ValidatorInterface $validator,
        \Magento\Framework\Session\StorageInterface $storage,
        \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager,
        \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory $cookieMetadataFactory,
        \Magento\Sales\Model\QuoteRepository $quoteRepository,
        CustomerRepositoryInterface $customerRepository,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Framework\StoreManagerInterface $storeManager,
        GroupManagementInterface $groupManagement
    ) {
        $this->quoteRepository = $quoteRepository;
        $this->customerRepository = $customerRepository;
        $this->_orderFactory = $orderFactory;
        $this->_storeManager = $storeManager;
        $this->groupManagement = $groupManagement;
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
        if ($this->_quote === null) {
            $this->_quote = $this->quoteRepository->create();
            if ($this->getStoreId()) {
                if (!$this->getQuoteId()) {
                    $customerGroupId = $this->groupManagement->getDefaultGroup()->getId();
                    $this->_quote->setCustomerGroupId($customerGroupId)
                        ->setIsActive(false)
                        ->setStoreId($this->getStoreId());
                    $this->quoteRepository->save($this->_quote);
                    $this->setQuoteId($this->_quote->getId());
                } else {
                    $this->_quote = $this->quoteRepository->get($this->getQuoteId());
                    $this->_quote->setStoreId($this->getStoreId());
                }

                if ($this->getCustomerId()) {
                    $this->_quote->assignCustomer($this->customerRepository->getById($this->getCustomerId()));
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
