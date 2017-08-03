<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Product\Compare;

use Magento\Catalog\Model\Product;

/**
 * Catalog Compare Item Model
 *
 * @api
 *
 * @method \Magento\Catalog\Model\ResourceModel\Product\Compare\Item getResource()
 * @method \Magento\Catalog\Model\Product\Compare\Item setVisitorId(int $value)
 * @method \Magento\Catalog\Model\Product\Compare\Item setCustomerId(int $value)
 * @method int getProductId()
 * @method \Magento\Catalog\Model\Product\Compare\Item setProductId(int $value)
 * @method int getStoreId()
 * @method \Magento\Catalog\Model\Product\Compare\Item setStoreId(int $value)
 * @since 2.0.0
 */
class Item extends \Magento\Framework\Model\AbstractModel implements \Magento\Framework\DataObject\IdentityInterface
{
    /**
     * Model cache tag
     */
    const CACHE_TAG = 'compare_item';

    /**
     * Prefix of model events names
     *
     * @var string
     * @since 2.0.0
     */
    protected $_eventPrefix = 'catalog_compare_item';

    /**
     * Parameter name in event
     *
     * In observe method you can use $observer->getEvent()->getItem() in this case
     *
     * @var string
     * @since 2.0.0
     */
    protected $_eventObject = 'item';

    /**
     * Catalog product compare
     *
     * @var \Magento\Catalog\Helper\Product\Compare
     * @since 2.0.0
     */
    protected $_catalogProductCompare = null;

    /**
     * Customer session
     *
     * @var \Magento\Customer\Model\Session
     * @since 2.0.0
     */
    protected $_customerSession;

    /**
     * Customer visitor
     *
     * @var \Magento\Customer\Model\Visitor
     * @since 2.0.0
     */
    protected $_customerVisitor;

    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     * @since 2.0.0
     */
    protected $_storeManager;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Customer\Model\Visitor $customerVisitor
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Catalog\Helper\Product\Compare $catalogProductCompare
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Customer\Model\Visitor $customerVisitor,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Catalog\Helper\Product\Compare $catalogProductCompare,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->_storeManager = $storeManager;
        $this->_customerVisitor = $customerVisitor;
        $this->_customerSession = $customerSession;
        $this->_catalogProductCompare = $catalogProductCompare;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * Initialize resourse model
     *
     * @return void
     * @since 2.0.0
     */
    protected function _construct()
    {
        $this->_init(\Magento\Catalog\Model\ResourceModel\Product\Compare\Item::class);
    }

    /**
     * Retrieve Resource instance
     *
     * @return \Magento\Catalog\Model\ResourceModel\Product\Compare\Item
     * @since 2.0.0
     */
    protected function _getResource()
    {
        return parent::_getResource();
    }

    /**
     * Set current store before save
     *
     * @return $this
     * @since 2.0.0
     */
    public function beforeSave()
    {
        parent::beforeSave();
        if (!$this->hasStoreId()) {
            $this->setStoreId($this->_storeManager->getStore()->getId());
        }

        return $this;
    }

    /**
     * Set visitor
     *
     * @param int $visitorId
     * @return $this
     * @since 2.0.0
     */
    public function addVisitorId($visitorId)
    {
        $this->setVisitorId($visitorId);
        return $this;
    }

    /**
     * Load compare item by product
     *
     * @param Product|int $product
     * @return $this
     * @since 2.0.0
     */
    public function loadByProduct($product)
    {
        $this->_getResource()->loadByProduct($this, $product);
        return $this;
    }

    /**
     * Set product data
     *
     * @param Product|int $product
     * @return $this
     * @since 2.0.0
     */
    public function addProductData($product)
    {
        if ($product instanceof Product) {
            $this->setProductId($product->getId());
        } elseif (intval($product)) {
            $this->setProductId(intval($product));
        }

        return $this;
    }

    /**
     * Retrieve data for save
     *
     * @return array
     * @since 2.0.0
     */
    public function getDataForSave()
    {
        $data = [];
        $data['customer_id'] = $this->getCustomerId();
        $data['visitor_id'] = $this->getVisitorId();
        $data['product_id'] = $this->getProductId();

        return $data;
    }

    /**
     * Customer login bind process
     *
     * @return $this
     * @since 2.0.0
     */
    public function bindCustomerLogin()
    {
        $this->_getResource()->updateCustomerFromVisitor($this);

        $this->_catalogProductCompare->setCustomerId($this->getCustomerId())->calculate();
        return $this;
    }

    /**
     * Customer logout bind process
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @since 2.0.0
     */
    public function bindCustomerLogout(\Magento\Framework\Event\Observer $observer = null)
    {
        $this->_getResource()->purgeVisitorByCustomer($this);

        $this->_catalogProductCompare->calculate(true);
        return $this;
    }

    /**
     * Clean compare items
     *
     * @return $this
     * @since 2.0.0
     */
    public function clean()
    {
        $this->_getResource()->clean();
        return $this;
    }

    /**
     * Retrieve Customer Id if loggined
     *
     * @return int
     * @since 2.0.0
     */
    public function getCustomerId()
    {
        if (!$this->hasData('customer_id')) {
            $customerId = $this->_customerSession->getCustomerId();
            $this->setData('customer_id', $customerId);
        }
        return $this->getData('customer_id');
    }

    /**
     * Retrieve Visitor Id
     *
     * @return int
     * @since 2.0.0
     */
    public function getVisitorId()
    {
        if (!$this->hasData('visitor_id')) {
            $visitorId = $this->_customerVisitor->getId();
            $this->setData('visitor_id', $visitorId);
        }
        return $this->getData('visitor_id');
    }

    /**
     * Get identities
     *
     * @return array
     * @since 2.0.0
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }
}
