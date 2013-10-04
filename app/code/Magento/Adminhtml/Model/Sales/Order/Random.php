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
 * @package     Magento_Adminhtml
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Create random order
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 *
 */
namespace Magento\Adminhtml\Model\Sales\Order;

/**
 * @SuppressWarnings(PHPMD.LongVariable)
 */
class Random
{
    /**
     * Quote model object
     *
     * @var \Magento\Sales\Model\Quote
     */
    protected $_quote;

    /**
     * Order model object
     *
     * @var \Magento\Sales\Model\Order
     */
    protected $_order;
    protected $_store;
    protected $_customer;
    protected $_productCollection;

    protected static $_storeCollection;
    protected static $_customerCollection;

    /**
     * @var \Magento\Core\Model\Resource\Store\CollectionFactory
     */
    protected $_storeCollectionFactory;

    /**
     * @var \Magento\Customer\Model\Resource\Customer\CollectionFactory
     */
    protected $_customerCollectionFactory;

    /**
     * @var \Magento\Catalog\Model\Resource\Product\CollectionFactory
     */
    protected $_productCollectionFactory;

    /**
     * @var \Magento\Catalog\Model\Product\Visibility
     */
    protected $_productVisibility;

    /**
     * @param \Magento\Catalog\Model\Product\Visibility $productVisibility
     * @param \Magento\Catalog\Model\Resource\Product\CollectionFactory $productCollectionFactory
     * @param \Magento\Customer\Model\Resource\Customer\CollectionFactory $customerCollectionFactory
     * @param \Magento\Core\Model\Resource\Store\CollectionFactory $storeCollectionFactory
     * @param \Magento\Sales\Model\QuoteFactory $quoteFactory
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     */
    public function __construct(
        \Magento\Catalog\Model\Product\Visibility $productVisibility,
        \Magento\Catalog\Model\Resource\Product\CollectionFactory $productCollectionFactory,
        \Magento\Customer\Model\Resource\Customer\CollectionFactory $customerCollectionFactory,
        \Magento\Core\Model\Resource\Store\CollectionFactory $storeCollectionFactory,
        \Magento\Sales\Model\QuoteFactory $quoteFactory,
        \Magento\Sales\Model\OrderFactory $orderFactory
    ) {
        $this->_productVisibility = $productVisibility;
        $this->_productCollectionFactory = $productCollectionFactory;
        $this->_customerCollectionFactory = $customerCollectionFactory;
        $this->_storeCollectionFactory = $storeCollectionFactory;
        $this->_quote = $quoteFactory->create()->save();
        $this->_order = $orderFactory->create();
    }

    protected function _getStores()
    {
        if (!self::$_storeCollection) {
            self::$_storeCollection = $this->_storeCollectionFactory->create()
                ->load();
        }
        return self::$_storeCollection->getItems();
    }

    protected function _getCustomers()
    {
        if (!self::$_customerCollection) {
            self::$_customerCollection = $this->_customerCollectionFactory->create()
                ->joinAttribute('billing_country_id', 'customer_address/country_id', 'default_billing', null, 'inner')
                ->joinAttribute('shipping_country_id', 'customer_address/country_id', 'default_shipping', null, 'inner')
                ->load();
        }
        return self::$_customerCollection->getItems();
    }

    protected function _getProducts()
    {
        if (!$this->_productCollection) {
            $this->_productCollection= $this->_productCollectionFactory->create();
            //$this->_productCollection->getEntity()->setStore($this->_getStore());
            $this->_productCollection->addAttributeToSelect('name')
                ->addAttributeToSelect('sku')
                ->addAttributeToFilter('type_id', \Magento\Catalog\Model\Product\Type::TYPE_SIMPLE)
                ->setVisibility($this->_productVisibility->getVisibleInSearchIds())
                ->load();
        }
        return $this->_productCollection->getItems();
    }

    /**
     * Retrieve customer model
     *
     * @return \Magento\Customer\Model\Customer
     */
    protected function _getCustomer()
    {
        if (!$this->_customer) {
            $items = $this->_getCustomers();
            $randKey = array_rand($items);
            $this->_customer = $items[$randKey];
        }
        return $this->_customer;
    }

    protected function _getRandomProduct()
    {
        $items = $this->_getProducts();
        $randKey = array_rand($items);
        return isset($items[$randKey]) ? $items[$randKey] : false;
    }

    protected function _getStore()
    {
        if (!$this->_store) {
            $items = $this->_getStores();
            $randKey = array_rand($items);
            $this->_store = $items[$randKey];
        }
        return $this->_store;
    }

    public function render()
    {
        $customer = $this->_getCustomer();
        $this->_quote->setStore($this->_getStore())
            ->setCustomer($customer);
        $this->_quote->getBillingAddress()->importCustomerAddress($customer->getDefaultBillingAddress());
        $this->_quote->getShippingAddress()->importCustomerAddress($customer->getDefaultShippingAddress());

        $productCount = rand(3, 10);
        for ($i=0; $i<$productCount; $i++){
            $product = $this->_getRandomProduct();
            if ($product) {
                $product->setQuoteQty(1);
                $this->_quote->addCatalogProduct($product);
            }
        }
        $this->_quote->getPayment()->setMethod('checkmo');

        $this->_quote->getShippingAddress()->setShippingMethod('freeshipping_freeshipping');//->collectTotals()->save();
        $this->_quote->getShippingAddress()->setCollectShippingRates(true);
        $this->_quote->collectTotals()
            ->save();
        $this->_quote->save();
        return $this;
    }

    protected function _getRandomDate()
    {
        $timestamp = mktime(rand(0,23), rand(0,59), 0, rand(1,11), rand(1,28), rand(2006, 2007));
        return date('Y-m-d H:i:s', $timestamp);
    }

    public function save()
    {
        $this->_order->setStoreId($this->_getStore()->getId());
        $this->_order->createFromQuoteAddress($this->_quote->getShippingAddress());
        $this->_order->validate();
        $this->_order->setInitialStatus();
        $this->_order->save();
        $this->_order->setCreatedAt($this->_getRandomDate());
        $this->_order->save();

        $this->_quote->setIsActive(false);
        $this->_quote->save();
        return $this;
    }
}
