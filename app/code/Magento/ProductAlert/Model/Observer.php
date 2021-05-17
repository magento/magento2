<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ProductAlert\Model;

use Magento\Backend\App\Area\FrontNameResolver;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Helper\Data;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Framework\Stdlib\DateTime\DateTimeFactory;
use Magento\Framework\Translate\Inline\StateInterface;
use Magento\ProductAlert\Model\ResourceModel\Stock\CollectionFactory as StockCollectionFactory;
use Magento\ProductAlert\Model\ResourceModel\Price\CollectionFactory as PriceCollectionFactory;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\Website;

/**
 * ProductAlert observer
 *
 * @author Magento Core Team <core@magentocommerce.com>
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Observer
{
    /**
     * Error email template configuration
     */
    const XML_PATH_ERROR_TEMPLATE = 'catalog/productalert_cron/error_email_template';

    /**
     * Error email identity configuration
     */
    const XML_PATH_ERROR_IDENTITY = 'catalog/productalert_cron/error_email_identity';

    /**
     * 'Send error emails to' configuration
     */
    const XML_PATH_ERROR_RECIPIENT = 'catalog/productalert_cron/error_email';

    /**
     * Allow price alert
     *
     */
    const XML_PATH_PRICE_ALLOW = 'catalog/productalert/allow_price';

    /**
     * Allow stock alert
     *
     */
    const XML_PATH_STOCK_ALLOW = 'catalog/productalert/allow_stock';

    /**
     * Default value of bunch size to load alert items
     */
    private const DEFAULT_BUNCH_SIZE = 10000;

    /**
     * Website collection array
     *
     * @var array
     */
    protected $_websites;

    /**
     * Warning (exception) errors array
     *
     * @var array
     */
    protected $_errors = [];

    /**
     * Catalog data
     *
     * @var Data
     */
    protected $_catalogData = null;

    /**
     * Core store config
     *
     * @var ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var PriceCollectionFactory
     */
    protected $_priceColFactory;

    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var DateTimeFactory
     */
    protected $_dateFactory;

    /**
     * @var StockCollectionFactory
     */
    protected $_stockColFactory;

    /**
     * @var TransportBuilder
     */
    protected $_transportBuilder;

    /**
     * @var EmailFactory
     */
    protected $_emailFactory;

    /**
     * @var StateInterface
     */
    protected $inlineTranslation;

    /**
     * @var ProductSalability
     */
    protected $productSalability;

    /**
     * @var int
     */
    private $bunchSize;

    /**
     * @param Data $catalogData
     * @param ScopeConfigInterface $scopeConfig
     * @param StoreManagerInterface $storeManager
     * @param PriceCollectionFactory $priceColFactory
     * @param CustomerRepositoryInterface $customerRepository
     * @param ProductRepositoryInterface $productRepository
     * @param DateTimeFactory $dateFactory
     * @param StockCollectionFactory $stockColFactory
     * @param TransportBuilder $transportBuilder
     * @param EmailFactory $emailFactory
     * @param StateInterface $inlineTranslation
     * @param ProductSalability|null $productSalability
     * @param int $bunchSize
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        Data $catalogData,
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        PriceCollectionFactory $priceColFactory,
        CustomerRepositoryInterface $customerRepository,
        ProductRepositoryInterface $productRepository,
        DateTimeFactory $dateFactory,
        StockCollectionFactory $stockColFactory,
        TransportBuilder $transportBuilder,
        EmailFactory $emailFactory,
        StateInterface $inlineTranslation,
        ProductSalability $productSalability = null,
        int $bunchSize = 0
    ) {
        $this->_catalogData = $catalogData;
        $this->_scopeConfig = $scopeConfig;
        $this->_storeManager = $storeManager;
        $this->_priceColFactory = $priceColFactory;
        $this->customerRepository = $customerRepository;
        $this->productRepository = $productRepository;
        $this->_dateFactory = $dateFactory;
        $this->_stockColFactory = $stockColFactory;
        $this->_transportBuilder = $transportBuilder;
        $this->_emailFactory = $emailFactory;
        $this->inlineTranslation = $inlineTranslation;
        $objectManager = ObjectManager::getInstance();
        $this->productSalability = $productSalability ?: $objectManager->get(ProductSalability::class);
        $this->bunchSize = $bunchSize ?: self::DEFAULT_BUNCH_SIZE;
    }

    /**
     * Retrieve website collection array
     *
     * @return array
     * @throws \Exception
     */
    protected function _getWebsites()
    {
        if ($this->_websites === null) {
            try {
                $this->_websites = $this->_storeManager->getWebsites();
            } catch (\Exception $e) {
                $this->_errors[] = $e->getMessage();
                throw $e;
            }
        }
        return $this->_websites;
    }

    /**
     * Process price emails
     *
     * @param Email $email
     * @return $this
     * @throws \Exception
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function _processPrice(Email $email)
    {
        $email->setType('price');
        foreach ($this->_getWebsites() as $website) {
            /* @var $website Website */
            if (!$website->getDefaultGroup() || !$website->getDefaultGroup()->getDefaultStore()) {
                continue;
            }
            if (!$this->_scopeConfig->getValue(
                    self::XML_PATH_PRICE_ALLOW,
                    ScopeInterface::SCOPE_STORE,
                    $website->getDefaultGroup()->getDefaultStore()->getId()
                )
            ) {
                continue;
            }
            try {
                $collection = $this->_priceColFactory->create()
                    ->addWebsiteFilter($website->getId())
                    ->setCustomerOrder()
                    ->addOrder('product_id');
            } catch (\Exception $e) {
                $this->_errors[] = $e->getMessage();
                throw $e;
            }

            $previousCustomer = null;
            $email->setWebsite($website);
            foreach ($this->loadItems($collection, $this->bunchSize) as $alert) {
                $this->setAlertStoreId($alert, $email);
                try {
                    if (!$previousCustomer || $previousCustomer->getId() != $alert->getCustomerId()) {
                        $customer = $this->customerRepository->getById($alert->getCustomerId());
                        if ($previousCustomer) {
                            $email->send();
                        }
                        if (!$customer) {
                            continue;
                        }
                        $previousCustomer = $customer;
                        $email->clean();
                        $email->setCustomerData($customer);
                    } else {
                        $customer = $previousCustomer;
                    }

                    $product = $this->productRepository->getById(
                        $alert->getProductId(),
                        false,
                        $website->getDefaultStore()->getId()
                    );

                    $product->setCustomerGroupId($customer->getGroupId());
                    if ($alert->getPrice() > $product->getFinalPrice()) {
                        $productPrice = $product->getFinalPrice();
                        $product->setFinalPrice($this->_catalogData->getTaxPrice($product, $productPrice));
                        $product->setPrice($this->_catalogData->getTaxPrice($product, $product->getPrice()));
                        $email->addPriceProduct($product);

                        $alert->setPrice($productPrice);
                        $alert->setLastSendDate($this->_dateFactory->create()->gmtDate());
                        $alert->setSendCount($alert->getSendCount() + 1);
                        $alert->setStatus(1);
                        $alert->save();
                    }
                } catch (\Exception $e) {
                    $this->_errors[] = $e->getMessage();
                    throw $e;
                }
            }
            if ($previousCustomer) {
                try {
                    $email->send();
                } catch (\Exception $e) {
                    $this->_errors[] = $e->getMessage();
                    throw $e;
                }
            }
        }
        return $this;
    }

    /**
     * Process stock emails
     *
     * @param Email $email
     * @return $this
     * @throws \Exception
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function _processStock(Email $email)
    {
        $email->setType('stock');

        foreach ($this->_getWebsites() as $website) {
            /* @var $website Website */

            if (!$website->getDefaultGroup() || !$website->getDefaultGroup()->getDefaultStore()) {
                continue;
            }
            if (!$this->_scopeConfig->getValue(
                self::XML_PATH_STOCK_ALLOW,
                ScopeInterface::SCOPE_STORE,
                $website->getDefaultGroup()->getDefaultStore()->getId()
            )
            ) {
                continue;
            }
            try {
                $collection = $this->_stockColFactory->create()
                    ->addWebsiteFilter($website->getId())
                    ->addStatusFilter(0)
                    ->setCustomerOrder()
                    ->addOrder('product_id');
            } catch (\Exception $e) {
                $this->_errors[] = $e->getMessage();
                throw $e;
            }

            $previousCustomer = null;
            $email->setWebsite($website);
            foreach ($this->loadItems($collection, $this->bunchSize) as $alert) {
                $this->setAlertStoreId($alert, $email);
                try {
                    if (!$previousCustomer || $previousCustomer->getId() != $alert->getCustomerId()) {
                        $customer = $this->customerRepository->getById($alert->getCustomerId());
                        if ($previousCustomer) {
                            $email->send();
                        }
                        if (!$customer) {
                            continue;
                        }
                        $previousCustomer = $customer;
                        $email->clean();
                        $email->setCustomerData($customer);
                    } else {
                        $customer = $previousCustomer;
                    }

                    $product = $this->productRepository->getById(
                        $alert->getProductId(),
                        false,
                        $website->getDefaultStore()->getId()
                    );

                    $product->setCustomerGroupId($customer->getGroupId());

                    if ($this->productSalability->isSalable($product, $website)) {
                        $email->addStockProduct($product);

                        $alert->setSendDate($this->_dateFactory->create()->gmtDate());
                        $alert->setSendCount($alert->getSendCount() + 1);
                        $alert->setStatus(1);
                        $alert->save();
                    }
                } catch (\Exception $e) {
                    $this->_errors[] = $e->getMessage();
                    throw $e;
                }
            }

            if ($previousCustomer) {
                try {
                    $email->send();
                } catch (\Exception $e) {
                    $this->_errors[] = $e->getMessage();
                    throw $e;
                }
            }
        }

        return $this;
    }

    /**
     * Send email to administrator if error
     *
     * @return $this
     */
    protected function _sendErrorEmail()
    {
        if (count($this->_errors)) {
            if (!$this->_scopeConfig->getValue(
                self::XML_PATH_ERROR_TEMPLATE,
                ScopeInterface::SCOPE_STORE
            )
            ) {
                return $this;
            }

            $this->inlineTranslation->suspend();

            $transport = $this->_transportBuilder->setTemplateIdentifier(
                $this->_scopeConfig->getValue(
                    self::XML_PATH_ERROR_TEMPLATE,
                    ScopeInterface::SCOPE_STORE
                )
            )->setTemplateOptions(
                [
                    'area' => FrontNameResolver::AREA_CODE,
                    'store' => Store::DEFAULT_STORE_ID,
                ]
            )->setTemplateVars(
                ['warnings' => join("\n", $this->_errors)]
            )->setFrom(
                $this->_scopeConfig->getValue(
                    self::XML_PATH_ERROR_IDENTITY,
                    ScopeInterface::SCOPE_STORE
                )
            )->addTo(
                $this->_scopeConfig->getValue(
                    self::XML_PATH_ERROR_RECIPIENT,
                    ScopeInterface::SCOPE_STORE
                )
            )->getTransport();

            $transport->sendMessage();

            $this->inlineTranslation->resume();
            $this->_errors[] = [];
        }
        return $this;
    }

    /**
     * Run process send product alerts
     *
     * @return $this
     */
    public function process()
    {
        /* @var $email Email */
        $email = $this->_emailFactory->create();
        $this->_processPrice($email);
        $this->_processStock($email);
        $this->_sendErrorEmail();

        return $this;
    }

    /**
     * Set alert store id.
     *
     * @param Price|Stock $alert
     * @param Email $email
     * @return Observer
     */
    private function setAlertStoreId($alert, Email $email): Observer
    {
        $alertStoreId = $alert->getStoreId();
        if ($alertStoreId) {
            $email->setStoreId((int)$alertStoreId);
        }

        return $this;
    }

    /**
     * Load items by bunch size
     *
     * @param AbstractCollection $collection
     * @param int $bunchSize
     * @return \Generator
     */
    private function loadItems(AbstractCollection $collection, int $bunchSize): \Generator
    {
        $collection->setPageSize($bunchSize);
        $pageCount = $collection->getLastPageNumber();
        $curPage = 1;
        while ($curPage <= $pageCount) {
            $collection->clear();
            $collection->setCurPage($curPage);
            foreach ($collection as $item) {
                yield $item;
            }
            $curPage++;
        }
    }
}
