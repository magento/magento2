<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ProductAlert\Model;

/**
 * ProductAlert Email processor
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Email extends \Magento\Framework\Model\AbstractModel
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
     * @var \Magento\Store\Model\Website
     */
    protected $_website;

    /**
     * Customer model
     *
     * @var \Magento\Customer\Api\Data\CustomerInterface
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
     * @var \Magento\ProductAlert\Block\Email\Price
     */
    protected $_priceBlock;

    /**
     * Stock block
     *
     * @var \Magento\ProductAlert\Block\Email\Stock
     */
    protected $_stockBlock;

    /**
     * Product alert data
     *
     * @var \Magento\ProductAlert\Helper\Data
     */
    protected $_productAlertData = null;

    /**
     * Core store config
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @var \Magento\Store\Model\App\Emulation
     */
    protected $_appEmulation;

    /**
     * @var \Magento\Framework\Mail\Template\TransportBuilder
     */
    protected $_transportBuilder;

    /**
     * @var \Magento\Customer\Helper\View
     */
    protected $_customerHelper;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\ProductAlert\Helper\Data $productAlertData
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     * @param \Magento\Customer\Helper\View $customerHelper
     * @param \Magento\Store\Model\App\Emulation $appEmulation
     * @param \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\ProductAlert\Helper\Data $productAlertData,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Customer\Helper\View $customerHelper,
        \Magento\Store\Model\App\Emulation $appEmulation,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
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
     * @param \Magento\Store\Model\Website $website
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
     * @return $this
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
     * @return $this
     */
    public function setCustomerId($customerId)
    {
        $this->_customer = $this->customerRepository->getById($customerId);
        return $this;
    }

    /**
     * Set customer model
     *
     * @param \Magento\Customer\Api\Data\CustomerInterface $customer
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
     * @param \Magento\Catalog\Model\Product $product
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
     * @param \Magento\Catalog\Model\Product $product
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
     * @return \Magento\ProductAlert\Block\Email\Price
     */
    protected function _getPriceBlock()
    {
        if ($this->_priceBlock === null) {
            $this->_priceBlock = $this->_productAlertData->createBlock('Magento\ProductAlert\Block\Email\Price');
        }
        return $this->_priceBlock;
    }

    /**
     * Retrieve stock block
     *
     * @return \Magento\ProductAlert\Block\Email\Stock
     */
    protected function _getStockBlock()
    {
        if ($this->_stockBlock === null) {
            $this->_stockBlock = $this->_productAlertData->createBlock('Magento\ProductAlert\Block\Email\Stock');
        }
        return $this->_stockBlock;
    }

    /**
     * Send customer email
     *
     * @return bool
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function send()
    {
        if ($this->_website === null || $this->_customer === null) {
            return false;
        }
        if ($this->_type == 'price' && count(
            $this->_priceProducts
        ) == 0 || $this->_type == 'stock' && count(
            $this->_stockProducts
        ) == 0
        ) {
            return false;
        }
        if (!$this->_website->getDefaultGroup() || !$this->_website->getDefaultGroup()->getDefaultStore()) {
            return false;
        }

        if ($this->_customer->getStoreId() > 0) {
            $store = $this->_storeManager->getStore($this->_customer->getStoreId());
        } else {
            $store = $this->_website->getDefaultStore();
        }
        $storeId = $store->getId();

        if ($this->_type == 'price' && !$this->_scopeConfig->getValue(
            self::XML_PATH_EMAIL_PRICE_TEMPLATE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        )
        ) {
            return false;
        } elseif ($this->_type == 'stock' && !$this->_scopeConfig->getValue(
            self::XML_PATH_EMAIL_STOCK_TEMPLATE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        )
        ) {
            return false;
        }

        if ($this->_type != 'price' && $this->_type != 'stock') {
            return false;
        }

        $this->_appEmulation->startEnvironmentEmulation($storeId);

        if ($this->_type == 'price') {
            $this->_getPriceBlock()->setStore($store)->reset();
            foreach ($this->_priceProducts as $product) {
                $product->setCustomerGroupId($this->_customer->getGroupId());
                $this->_getPriceBlock()->addProduct($product);
            }
            $block = $this->_getPriceBlock();
            $templateId = $this->_scopeConfig->getValue(
                self::XML_PATH_EMAIL_PRICE_TEMPLATE,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                $storeId
            );
        } else {
            $this->_getStockBlock()->setStore($store)->reset();
            foreach ($this->_stockProducts as $product) {
                $product->setCustomerGroupId($this->_customer->getGroupId());
                $this->_getStockBlock()->addProduct($product);
            }
            $block = $this->_getStockBlock();
            $templateId = $this->_scopeConfig->getValue(
                self::XML_PATH_EMAIL_STOCK_TEMPLATE,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                $storeId
            );
        }

        $alertGrid = $this->_appState->emulateAreaCode(
            \Magento\Framework\App\Area::AREA_FRONTEND,
            [$block, 'toHtml']
        );
        $this->_appEmulation->stopEnvironmentEmulation();

        $transport = $this->_transportBuilder->setTemplateIdentifier(
            $templateId
        )->setTemplateOptions(
            ['area' => \Magento\Framework\App\Area::AREA_FRONTEND, 'store' => $storeId]
        )->setTemplateVars(
            [
                'customerName' => $this->_customerHelper->getCustomerName($this->_customer),
                'alertGrid' => $alertGrid,
            ]
        )->setFrom(
            $this->_scopeConfig->getValue(
                self::XML_PATH_EMAIL_IDENTITY,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                $storeId
            )
        )->addTo(
            $this->_customer->getEmail(),
            $this->_customerHelper->getCustomerName($this->_customer)
        )->getTransport();

        $transport->sendMessage();

        return true;
    }
}
