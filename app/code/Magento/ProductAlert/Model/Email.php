<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ProductAlert\Model;

use Magento\Catalog\Model\Product;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Helper\View;
use Magento\Framework\App\Area;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\MailException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\ProductAlert\Block\Email\AbstractEmail;
use Magento\ProductAlert\Block\Email\Price;
use Magento\ProductAlert\Block\Email\Stock;
use Magento\ProductAlert\Helper\Data;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\App\Emulation;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\Website;

/**
 * ProductAlert Email processor
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 *
 * @api
 * @since 100.0.2
 * @method int getStoreId()
 * @method $this setStoreId()
 */
class Email extends AbstractModel
{
    const XML_PATH_EMAIL_PRICE_TEMPLATE = 'catalog/productalert/email_price_template';

    const XML_PATH_EMAIL_STOCK_TEMPLATE = 'catalog/productalert/email_stock_template';

    const XML_PATH_EMAIL_IDENTITY = 'catalog/productalert/email_identity';

    /**
     * Type
     *
     * @var string
     */
    protected $_type = 'price';

    /**
     * Website Model
     *
     * @var Website
     */
    protected $_website;

    /**
     * Customer model
     *
     * @var CustomerInterface
     */
    protected $_customer;

    /**
     * Products collection where changed price
     *
     * @var array
     */
    protected $_priceProducts = [];

    /**
     * Product collection which of back in stock
     *
     * @var array
     */
    protected $_stockProducts = [];

    /**
     * Price block
     *
     * @var Price
     */
    protected $_priceBlock;

    /**
     * Stock block
     *
     * @var Stock
     */
    protected $_stockBlock;

    /**
     * Product alert data
     *
     * @var Data
     */
    protected $_productAlertData = null;

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
     * @var CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @var Emulation
     */
    protected $_appEmulation;

    /**
     * @var TransportBuilder
     */
    protected $_transportBuilder;

    /**
     * @var View
     */
    protected $_customerHelper;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param Data $productAlertData
     * @param ScopeConfigInterface $scopeConfig
     * @param StoreManagerInterface $storeManager
     * @param CustomerRepositoryInterface $customerRepository
     * @param View $customerHelper
     * @param Emulation $appEmulation
     * @param TransportBuilder $transportBuilder
     * @param AbstractResource $resource
     * @param AbstractDb $resourceCollection
     * @param array $data
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        Context $context,
        Registry $registry,
        Data $productAlertData,
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        CustomerRepositoryInterface $customerRepository,
        View $customerHelper,
        Emulation $appEmulation,
        TransportBuilder $transportBuilder,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->_productAlertData = $productAlertData;
        $this->_scopeConfig = $scopeConfig;
        $this->_storeManager = $storeManager;
        $this->customerRepository = $customerRepository;
        $this->_appEmulation = $appEmulation;
        $this->_transportBuilder = $transportBuilder;
        $this->_customerHelper = $customerHelper;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * Set model type
     *
     * @param string $type
     *
     * @return void
     */
    public function setType($type)
    {
        $this->_type = $type;
    }

    /**
     * Retrieve model type
     *
     * @return string
     */
    public function getType()
    {
        return $this->_type;
    }

    /**
     * Set website model
     *
     * @param Website $website
     *
     * @return $this
     */
    public function setWebsite(\Magento\Store\Model\Website $website)
    {
        $this->_website = $website;
        return $this;
    }

    /**
     * Set website id
     *
     * @param int $websiteId
     *
     * @return $this
     * @throws LocalizedException
     */
    public function setWebsiteId($websiteId)
    {
        $this->_website = $this->_storeManager->getWebsite($websiteId);
        return $this;
    }

    /**
     * Set customer by id
     *
     * @param int $customerId
     *
     * @return $this
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function setCustomerId($customerId)
    {
        $this->_customer = $this->customerRepository->getById($customerId);
        return $this;
    }

    /**
     * Set customer model
     *
     * @param CustomerInterface $customer
     *
     * @return $this
     */
    public function setCustomerData($customer)
    {
        $this->_customer = $customer;
        return $this;
    }

    /**
     * Clean data
     *
     * @return $this
     */
    public function clean()
    {
        $this->_customer = null;
        $this->_priceProducts = [];
        $this->_stockProducts = [];

        return $this;
    }

    /**
     * Add product (price change) to collection
     *
     * @param Product $product
     *
     * @return $this
     */
    public function addPriceProduct(\Magento\Catalog\Model\Product $product)
    {
        $this->_priceProducts[$product->getId()] = $product;
        return $this;
    }

    /**
     * Add product (back in stock) to collection
     *
     * @param Product $product
     *
     * @return $this
     */
    public function addStockProduct(\Magento\Catalog\Model\Product $product)
    {
        $this->_stockProducts[$product->getId()] = $product;
        return $this;
    }

    /**
     * Retrieve price block
     *
     * @return Price
     * @throws LocalizedException
     */
    protected function _getPriceBlock()
    {
        if ($this->_priceBlock === null) {
            $this->_priceBlock = $this->_productAlertData->createBlock(Price::class);
        }
        return $this->_priceBlock;
    }

    /**
     * Retrieve stock block
     *
     * @return Stock
     * @throws LocalizedException
     */
    protected function _getStockBlock()
    {
        if ($this->_stockBlock === null) {
            $this->_stockBlock = $this->_productAlertData->createBlock(Stock::class);
        }
        return $this->_stockBlock;
    }

    /**
     * Send customer email
     *
     * @return bool
     * @throws MailException
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function send()
    {
        if ($this->_website === null || $this->_customer === null || !$this->isExistDefaultStore()) {
            return false;
        }

        $products = $this->getProducts();
        $templateConfigPath = $this->getTemplateConfigPath();
        if (!in_array($this->_type, ['price', 'stock']) || count($products) === 0 || !$templateConfigPath) {
            return false;
        }

        $storeId = $this->getStoreId() ?: (int) $this->_customer->getStoreId();
        $store = $this->getStore($storeId);

        $this->_appEmulation->startEnvironmentEmulation($storeId);

        $block = $this->getBlock();
        $block->setStore($store)->reset();

        // Add products to the block
        foreach ($products as $product) {
            $product->setCustomerGroupId($this->_customer->getGroupId());
            $block->addProduct($product);
        }

        $templateId = $this->_scopeConfig->getValue(
            $templateConfigPath,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );

        $alertGrid = $this->_appState->emulateAreaCode(
            Area::AREA_FRONTEND,
            [$block, 'toHtml']
        );
        $this->_appEmulation->stopEnvironmentEmulation();

        $customerName = $this->_customerHelper->getCustomerName($this->_customer);
        $this->_transportBuilder->setTemplateIdentifier(
            $templateId
        )->setTemplateOptions(
            ['area' => Area::AREA_FRONTEND, 'store' => $storeId]
        )->setTemplateVars(
            [
                'customerName' => $customerName,
                'alertGrid' => $alertGrid,
            ]
        )->setFrom(
            $this->_scopeConfig->getValue(
                self::XML_PATH_EMAIL_IDENTITY,
                ScopeInterface::SCOPE_STORE,
                $storeId
            )
        )->addTo(
            $this->_customer->getEmail(),
            $customerName
        )->getTransport()->sendMessage();

        return true;
    }

    /**
     * Retrieve the store for the email
     *
     * @param int $storeId
     * @return StoreInterface
     * @throws NoSuchEntityException
     */
    private function getStore(int $storeId): StoreInterface
    {
        return $this->_storeManager->getStore($storeId);
    }

    /**
     * Retrieve the block for the email based on type
     *
     * @return Price|Stock
     * @throws LocalizedException
     */
    private function getBlock(): AbstractEmail
    {
        return $this->_type === 'price'
            ? $this->_getPriceBlock()
            : $this->_getStockBlock();
    }

    /**
     * Retrieve the products for the email based on type
     *
     * @return array
     */
    private function getProducts(): array
    {
        return $this->_type === 'price'
            ? $this->_priceProducts
            : $this->_stockProducts;
    }

    /**
     * Retrieve template config path based on type
     *
     * @return string
     */
    private function getTemplateConfigPath(): string
    {
        return $this->_type === 'price'
            ? self::XML_PATH_EMAIL_PRICE_TEMPLATE
            : self::XML_PATH_EMAIL_STOCK_TEMPLATE;
    }

    /**
     * Check if exists default store.
     *
     * @return bool
     */
    private function isExistDefaultStore(): bool
    {
        if (!$this->_website->getDefaultGroup() || !$this->_website->getDefaultGroup()->getDefaultStore()) {
            return false;
        }
        return true;
    }
}
