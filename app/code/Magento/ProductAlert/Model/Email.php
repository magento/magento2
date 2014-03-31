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
 * @package     Magento_ProductAlert
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\ProductAlert\Model;

/**
 * ProductAlert Email processor
 *
 * @category   Magento
 * @package    Magento_ProductAlert
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Email extends \Magento\Model\AbstractModel
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
     * @var \Magento\Core\Model\Website
     */
    protected $_website;

    /**
     * Customer model
     *
     * @var \Magento\Customer\Model\Customer
     */
    protected $_customer;

    /**
     * Products collection where changed price
     *
     * @var array
     */
    protected $_priceProducts = array();

    /**
     * Product collection which of back in stock
     *
     * @var array
     */
    protected $_stockProducts = array();

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
     * @var \Magento\Core\Model\Store\Config
     */
    protected $_coreStoreConfig;

    /**
     * @var \Magento\Core\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $_customerFactory;

    /**
     * @var \Magento\Core\Model\App\Emulation
     */
    protected $_appEmulation;

    /**
     * @var \Magento\Mail\Template\TransportBuilder
     */
    protected $_transportBuilder;

    /**
     * @param \Magento\Model\Context $context
     * @param \Magento\Registry $registry
     * @param \Magento\ProductAlert\Helper\Data $productAlertData
     * @param \Magento\Core\Model\Store\Config $coreStoreConfig
     * @param \Magento\Core\Model\StoreManagerInterface $storeManager
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param \Magento\Core\Model\App\Emulation $appEmulation
     * @param \Magento\Mail\Template\TransportBuilder $transportBuilder
     * @param \Magento\Model\Resource\AbstractResource $resource
     * @param \Magento\Data\Collection\Db $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Model\Context $context,
        \Magento\Registry $registry,
        \Magento\ProductAlert\Helper\Data $productAlertData,
        \Magento\Core\Model\Store\Config $coreStoreConfig,
        \Magento\Core\Model\StoreManagerInterface $storeManager,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Core\Model\App\Emulation $appEmulation,
        \Magento\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Model\Resource\AbstractResource $resource = null,
        \Magento\Data\Collection\Db $resourceCollection = null,
        array $data = array()
    ) {
        $this->_productAlertData = $productAlertData;
        $this->_coreStoreConfig = $coreStoreConfig;
        $this->_storeManager = $storeManager;
        $this->_customerFactory = $customerFactory;
        $this->_appEmulation = $appEmulation;
        $this->_transportBuilder = $transportBuilder;
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
     * @param \Magento\Core\Model\Website $website
     * @return $this
     */
    public function setWebsite(\Magento\Core\Model\Website $website)
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
        $this->_customer = $this->_customerFactory->create()->load($customerId);
        return $this;
    }

    /**
     * Set customer model
     *
     * @param \Magento\Customer\Model\Customer $customer
     * @return $this
     */
    public function setCustomer(\Magento\Customer\Model\Customer $customer)
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
        $this->_priceProducts = array();
        $this->_stockProducts = array();

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
        if (is_null($this->_priceBlock)) {
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
        if (is_null($this->_stockBlock)) {
            $this->_stockBlock = $this->_productAlertData->createBlock('Magento\ProductAlert\Block\Email\Stock');
        }
        return $this->_stockBlock;
    }

    /**
     * Send customer email
     *
     * @return bool
     */
    public function send()
    {
        if (is_null($this->_website) || is_null($this->_customer)) {
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

        $store = $this->_website->getDefaultStore();
        $storeId = $store->getId();

        if ($this->_type == 'price' && !$this->_coreStoreConfig->getConfig(
            self::XML_PATH_EMAIL_PRICE_TEMPLATE,
            $storeId
        )
        ) {
            return false;
        } elseif ($this->_type == 'stock' && !$this->_coreStoreConfig->getConfig(
            self::XML_PATH_EMAIL_STOCK_TEMPLATE,
            $storeId
        )
        ) {
            return false;
        }

        if ($this->_type != 'price' && $this->_type != 'stock') {
            return false;
        }

        $initialEnvironmentInfo = $this->_appEmulation->startEnvironmentEmulation($storeId);

        if ($this->_type == 'price') {
            $this->_getPriceBlock()->setStore($store)->reset();
            foreach ($this->_priceProducts as $product) {
                $product->setCustomerGroupId($this->_customer->getGroupId());
                $this->_getPriceBlock()->addProduct($product);
            }
            $block = $this->_getPriceBlock()->toHtml();
            $templateId = $this->_coreStoreConfig->getConfig(self::XML_PATH_EMAIL_PRICE_TEMPLATE, $storeId);
        } else {
            $this->_getStockBlock()->setStore($store)->reset();
            foreach ($this->_stockProducts as $product) {
                $product->setCustomerGroupId($this->_customer->getGroupId());
                $this->_getStockBlock()->addProduct($product);
            }
            $block = $this->_getStockBlock()->toHtml();
            $templateId = $this->_coreStoreConfig->getConfig(self::XML_PATH_EMAIL_STOCK_TEMPLATE, $storeId);
        }

        $this->_appEmulation->stopEnvironmentEmulation($initialEnvironmentInfo);

        $transport = $this->_transportBuilder->setTemplateIdentifier(
            $templateId
        )->setTemplateOptions(
            array('area' => \Magento\Core\Model\App\Area::AREA_FRONTEND, 'store' => $storeId)
        )->setTemplateVars(
            array('customerName' => $this->_customer->getName(), 'alertGrid' => $block)
        )->setFrom(
            $this->_coreStoreConfig->getConfig(self::XML_PATH_EMAIL_IDENTITY, $storeId)
        )->addTo(
            $this->_customer->getEmail(),
            $this->_customer->getName()
        )->getTransport();

        $transport->sendMessage();

        return true;
    }
}
