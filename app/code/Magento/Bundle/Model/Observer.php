<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Bundle\Model;

/**
 * Bundle Products Observer
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Observer
{
    /**
     * Bundle data
     *
     * @var \Magento\Bundle\Helper\Data
     */
    protected $_bundleData = null;

    /**
     * Catalog helper
     *
     * @var \Magento\Catalog\Helper\Catalog
     */
    protected $_helperCatalog = null;

    /**
     * @var \Magento\Bundle\Model\Resource\Selection
     */
    protected $_bundleSelection;

    /**
     * @var \Magento\Catalog\Model\Config
     */
    protected $_config;

    /**
     * @var \Magento\Catalog\Model\Product\Visibility
     */
    protected $_productVisibility;

    /**
     * @param \Magento\Catalog\Model\Product\Visibility $productVisibility
     * @param \Magento\Catalog\Model\Config $config
     * @param \Magento\Bundle\Model\Resource\Selection $bundleSelection
     * @param \Magento\Catalog\Helper\Catalog $helperCatalog
     * @param \Magento\Bundle\Helper\Data $bundleData
     */
    public function __construct(
        \Magento\Catalog\Model\Product\Visibility $productVisibility,
        \Magento\Catalog\Model\Config $config,
        \Magento\Bundle\Model\Resource\Selection $bundleSelection,
        \Magento\Catalog\Helper\Catalog $helperCatalog,
        \Magento\Bundle\Helper\Data $bundleData
    ) {
        $this->_helperCatalog = $helperCatalog;
        $this->_bundleData = $bundleData;
        $this->_bundleSelection = $bundleSelection;
        $this->_config = $config;
        $this->_productVisibility = $productVisibility;
    }

    /**
     * Append bundles in upsell list for current product
     *
     * @param \Magento\Framework\Object $observer
     * @return $this
     */
    public function appendUpsellProducts($observer)
    {
        /* @var $product \Magento\Catalog\Model\Product */
        $product = $observer->getEvent()->getProduct();

        /**
         * Check is current product type is allowed for bundle selection product type
         */
        if (!in_array($product->getTypeId(), $this->_bundleData->getAllowedSelectionTypes())) {
            return $this;
        }

        /* @var $collection \Magento\Catalog\Model\Resource\Product\Link\Product\Collection */
        $collection = $observer->getEvent()->getCollection();
        $limit = $observer->getEvent()->getLimit();
        if (is_array($limit)) {
            if (isset($limit['upsell'])) {
                $limit = $limit['upsell'];
            } else {
                $limit = 0;
            }
        }

        /* @var $resource \Magento\Bundle\Model\Resource\Selection */
        $resource = $this->_bundleSelection;

        $productIds = array_keys($collection->getItems());
        if (!is_null($limit) && $limit <= count($productIds)) {
            return $this;
        }

        // retrieve bundle product ids
        $bundleIds = $resource->getParentIdsByChild($product->getId());
        // exclude up-sell product ids
        $bundleIds = array_diff($bundleIds, $productIds);

        if (!$bundleIds) {
            return $this;
        }

        /* @var $bundleCollection \Magento\Catalog\Model\Resource\Product\Collection */
        $bundleCollection = $product->getCollection()->addAttributeToSelect(
            $this->_config->getProductAttributes()
        )->addStoreFilter()->addMinimalPrice()->addFinalPrice()->addTaxPercents()->setVisibility(
            $this->_productVisibility->getVisibleInCatalogIds()
        );

        if (!is_null($limit)) {
            $bundleCollection->setPageSize($limit);
        }
        $bundleCollection->addFieldToFilter(
            'entity_id',
            ['in' => $bundleIds]
        )->setFlag(
            'do_not_use_category_id',
            true
        );

        if ($collection instanceof \Magento\Framework\Data\Collection) {
            foreach ($bundleCollection as $item) {
                $collection->addItem($item);
            }
        } elseif ($collection instanceof \Magento\Framework\Object) {
            $items = $collection->getItems();
            foreach ($bundleCollection as $item) {
                $items[$item->getEntityId()] = $item;
            }
            $collection->setItems($items);
        }

        return $this;
    }

    /**
     * Add price index data for catalog product collection
     * only for front end
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function loadProductOptions($observer)
    {
        $collection = $observer->getEvent()->getCollection();
        /* @var $collection \Magento\Catalog\Model\Resource\Product\Collection */
        $collection->addPriceData();

        return $this;
    }

    /**
     * Setting attribute tab block for bundle
     *
     * @param \Magento\Framework\Object $observer
     * @return $this
     */
    public function setAttributeTabBlock($observer)
    {
        $product = $observer->getEvent()->getProduct();
        if ($product->getTypeId() == \Magento\Catalog\Model\Product\Type::TYPE_BUNDLE) {
            $this->_helperCatalog->setAttributeTabBlock(
                'Magento\Bundle\Block\Adminhtml\Catalog\Product\Edit\Tab\Attributes'
            );
        }
        return $this;
    }

    /**
     * Initialize product options renderer with bundle specific params
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function initOptionRenderer(\Magento\Framework\Event\Observer $observer)
    {
        $block = $observer->getBlock();
        $block->addOptionsRenderCfg('bundle', 'Magento\Bundle\Helper\Catalog\Product\Configuration');
        return $this;
    }
}
