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
 * @package     Magento_Catalog
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Catalog\Model\Product\Flat;

/**
 * Catalog Product Flat observer
 *
 * @SuppressWarnings(PHPMD.LongVariable)
 */
class Observer
{
    /**
     * Catalog product flat
     *
     * @var \Magento\Catalog\Helper\Product\Flat
     */
    protected $_catalogProductFlat = null;

    /**
     * Catalog product flat indexer
     *
     * @var \Magento\Catalog\Model\Product\Flat\Indexer
     */
    protected $_catalogProductFlatIndexer;

    /**
     * Store manager
     *
     * @var \Magento\Core\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * Construct
     *
     * @param \Magento\Core\Model\StoreManagerInterface $storeManager
     * @param \Magento\Catalog\Model\Product\Flat\Indexer $catalogProductFlatIndexer
     * @param \Magento\Catalog\Helper\Product\Flat $catalogProductFlat
     */
    public function __construct(
        \Magento\Core\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\Product\Flat\Indexer $catalogProductFlatIndexer,
        \Magento\Catalog\Helper\Product\Flat $catalogProductFlat
    ) {
        $this->_storeManager = $storeManager;
        $this->_catalogProductFlatIndexer = $catalogProductFlatIndexer;
        $this->_catalogProductFlat = $catalogProductFlat;
    }

    /**
     * Retrieve Catalog Product Flat Helper
     *
     * @return \Magento\Catalog\Helper\Product\Flat
     */
    protected function _getHelper()
    {
        return $this->_catalogProductFlat;
    }

    /**
     * Retrieve Catalog Product Flat Indexer model
     *
     * @return \Magento\Catalog\Model\Product\Flat\Indexer
     */
    protected function _getIndexer() {
        return $this->_catalogProductFlatIndexer;
    }

    /**
     * Catalog Entity attribute after save process
     *
     * @param \Magento\Event\Observer $observer
     * @return \Magento\Catalog\Model\Product\Flat\Observer
     */
    public function catalogEntityAttributeSaveAfter(\Magento\Event\Observer $observer)
    {
        if (!$this->_getHelper()->isAvailable() || !$this->_getHelper()->isBuilt()) {
            return $this;
        }

        $attribute = $observer->getEvent()->getAttribute();
        /* @var $attribute \Magento\Catalog\Model\Entity\Attribute */

        $enableBefore   = ($attribute->getOrigData('backend_type') == 'static')
            || ($this->_getHelper()->isAddFilterableAttributes() && $attribute->getOrigData('is_filterable') > 0)
            || ($attribute->getOrigData('used_in_product_listing') == 1)
            || ($attribute->getOrigData('used_for_sort_by') == 1);
        $enableAfter    = ($attribute->getData('backend_type') == 'static')
            || ($this->_getHelper()->isAddFilterableAttributes() && $attribute->getData('is_filterable') > 0)
            || ($attribute->getData('used_in_product_listing') == 1)
            || ($attribute->getData('used_for_sort_by') == 1);

        if (!$enableAfter && !$enableBefore) {
            return $this;
        }

        if ($enableBefore && !$enableAfter) {
            // delete attribute data from flat
            $this->_getIndexer()->prepareDataStorage();
        }
        else {
            $this->_getIndexer()->updateAttribute($attribute->getAttributeCode());
        }

        return $this;
    }

    /**
     * Catalog Product Status Update
     *
     * @param \Magento\Event\Observer $observer
     * @return \Magento\Catalog\Model\Product\Flat\Observer
     */
    public function catalogProductStatusUpdate(\Magento\Event\Observer $observer)
    {
        if (!$this->_getHelper()->isAvailable() || !$this->_getHelper()->isBuilt()) {
            return $this;
        }

        $productId  = $observer->getEvent()->getProductId();
        $status     = $observer->getEvent()->getStatus();
        $storeId    = $observer->getEvent()->getStoreId();
        $storeId    = $storeId > 0 ? $storeId : null;

        $this->_getIndexer()->updateProductStatus($productId, $status, $storeId);

        return $this;
    }

    /**
     * Catalog Product Website(s) update
     *
     * @param \Magento\Event\Observer $observer
     * @return \Magento\Catalog\Model\Product\Flat\Observer
     */
    public function catalogProductWebsiteUpdate(\Magento\Event\Observer $observer)
    {
        if (!$this->_getHelper()->isAvailable() || !$this->_getHelper()->isBuilt()) {
            return $this;
        }

        $websiteIds = $observer->getEvent()->getWebsiteIds();
        $productIds = $observer->getEvent()->getProductIds();

        foreach ($websiteIds as $websiteId) {
            $website = $this->_storeManager->getWebsite($websiteId);
            foreach ($website->getStores() as $store) {
                if ($observer->getEvent()->getAction() == 'remove') {
                    $this->_getIndexer()->removeProduct($productIds, $store->getId());
                }
                else {
                    $this->_getIndexer()->updateProduct($productIds, $store->getId());
                }
            }
        }

        return $this;
    }

    /**
     * Catalog Product After Save
     *
     * @param \Magento\Event\Observer $observer
     * @return \Magento\Catalog\Model\Product\Flat\Observer
     */
    public function catalogProductSaveAfter(\Magento\Event\Observer $observer) {
        if (!$this->_getHelper()->isAvailable() || !$this->_getHelper()->isBuilt()) {
            return $this;
        }

        $product   = $observer->getEvent()->getProduct();
        $productId = $product->getId();

        $this->_getIndexer()->saveProduct($productId);

        return $this;
    }

    /**
     * Add new store flat process
     *
     * @param \Magento\Event\Observer $observer
     * @return \Magento\Catalog\Model\Product\Flat\Observer
     */
    public function storeAdd(\Magento\Event\Observer $observer)
    {
        if (!$this->_getHelper()->isAvailable() || !$this->_getHelper()->isBuilt()) {
            return $this;
        }

        $store = $observer->getEvent()->getStore();
        /* @var $store \Magento\Core\Model\Store */
        $this->_getIndexer()->rebuild($store->getId());

        return $this;
    }

    /**
     * Store edit action, check change store group
     *
     * @param \Magento\Event\Observer $observer
     * @return \Magento\Catalog\Model\Product\Flat\Observer
     */
    public function storeEdit(\Magento\Event\Observer $observer)
    {
        if (!$this->_getHelper()->isAvailable() || !$this->_getHelper()->isBuilt()) {
            return $this;
        }

        $store = $observer->getEvent()->getStore();
        /* @var $store \Magento\Core\Model\Store */
        if ($store->dataHasChangedFor('group_id')) {
            $this->_getIndexer()->rebuild($store->getId());
        }

        return $this;
    }

    /**
     * Store delete after process
     *
     * @param \Magento\Event\Observer $observer
     * @return \Magento\Catalog\Model\Product\Flat\Observer
     */
    public function storeDelete(\Magento\Event\Observer $observer)
    {
        if (!$this->_getHelper()->isAvailable() || !$this->_getHelper()->isBuilt()) {
            return $this;
        }

        $store = $observer->getEvent()->getStore();
        /* @var $store \Magento\Core\Model\Store */

        $this->_getIndexer()->deleteStore($store->getId());

        return $this;
    }

    /**
     * Store Group Save process
     *
     * @param \Magento\Event\Observer $observer
     * @return \Magento\Catalog\Model\Product\Flat\Observer
     */
    public function storeGroupSave(\Magento\Event\Observer $observer)
    {
        if (!$this->_getHelper()->isAvailable() || !$this->_getHelper()->isBuilt()) {
            return $this;
        }

        $group = $observer->getEvent()->getGroup();
        /* @var $group \Magento\Core\Model\Store\Group */

        if ($group->dataHasChangedFor('website_id')) {
            foreach ($group->getStores() as $store) {
                /* @var $store \Magento\Core\Model\Store */
                $this->_getIndexer()->rebuild($store->getId());
            }
        }

        return $this;
    }

    /**
     * Catalog Product Import After process
     *
     * @param \Magento\Event\Observer $observer
     * @return \Magento\Catalog\Model\Product\Flat\Observer
     */
    public function catalogProductImportAfter(\Magento\Event\Observer $observer)
    {
        if (!$this->_getHelper()->isAvailable() || !$this->_getHelper()->isBuilt()) {
            return $this;
        }

        $this->_getIndexer()->rebuild();

        return $this;
    }

    /**
     * Customer Group save after process
     *
     * @param \Magento\Event\Observer\Collection $observer
     * @return \Magento\Catalog\Model\Product\Flat\Observer
     */
    public function customerGroupSaveAfter(\Magento\Event\Observer $observer)
    {
        if (!$this->_getHelper()->isAvailable() || !$this->_getHelper()->isBuilt()) {
            return $this;
        }

        $customerGroup = $observer->getEvent()->getObject();
        /* @var $customerGroup \Magento\Customer\Model\Group */
        if ($customerGroup->dataHasChangedFor($customerGroup->getIdFieldName())
            || $customerGroup->dataHasChangedFor('tax_class_id')) {
            $this->_getIndexer()->updateEventAttributes();
        }
        return $this;
    }
}
