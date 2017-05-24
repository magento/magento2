<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Bundle\Observer;

use Magento\Framework\Event\ObserverInterface;

class AppendUpsellProductsObserver implements ObserverInterface
{
    /**
     * Bundle data
     *
     * @var \Magento\Bundle\Helper\Data
     */
    protected $bundleData;

    /**
     * @var \Magento\Bundle\Model\ResourceModel\Selection
     */
    protected $bundleSelection;

    /**
     * @var \Magento\Catalog\Model\Config
     */
    protected $config;

    /**
     * @var \Magento\Catalog\Model\Product\Visibility
     */
    protected $productVisibility;

    /**
     * @param \Magento\Bundle\Helper\Data $bundleData
     * @param \Magento\Catalog\Model\Product\Visibility $productVisibility
     * @param \Magento\Catalog\Model\Config $config
     * @param \Magento\Bundle\Model\ResourceModel\Selection $bundleSelection
     */
    public function __construct(
        \Magento\Bundle\Helper\Data $bundleData,
        \Magento\Catalog\Model\Product\Visibility $productVisibility,
        \Magento\Catalog\Model\Config $config,
        \Magento\Bundle\Model\ResourceModel\Selection $bundleSelection
    ) {
        $this->bundleData = $bundleData;
        $this->productVisibility = $productVisibility;
        $this->config = $config;
        $this->bundleSelection = $bundleSelection;
    }

    /**
     * Append bundles in upsell list for current product
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /* @var $product \Magento\Catalog\Model\Product */
        $product = $observer->getEvent()->getProduct();

        /**
         * Check is current product type is allowed for bundle selection product type
         */
        if (!in_array($product->getTypeId(), $this->bundleData->getAllowedSelectionTypes())) {
            return $this;
        }

        /* @var $collection \Magento\Catalog\Model\ResourceModel\Product\Link\Product\Collection */
        $collection = $observer->getEvent()->getCollection();
        $limit = $observer->getEvent()->getLimit();
        if (is_array($limit)) {
            if (isset($limit['upsell'])) {
                $limit = $limit['upsell'];
            } else {
                $limit = 0;
            }
        }

        /* @var $resource \Magento\Bundle\Model\ResourceModel\Selection */
        $resource = $this->bundleSelection;

        $productIds = array_keys($collection->getItems());
        if ($limit !== null && $limit <= count($productIds)) {
            return $this;
        }

        // retrieve bundle product ids
        $bundleIds = $resource->getParentIdsByChild($product->getId());
        // exclude up-sell product ids
        $bundleIds = array_diff($bundleIds, $productIds);

        if (!$bundleIds) {
            return $this;
        }

        /* @var $bundleCollection \Magento\Catalog\Model\ResourceModel\Product\Collection */
        $bundleCollection = $product->getCollection()->addAttributeToSelect(
            $this->config->getProductAttributes()
        )->addStoreFilter()->addMinimalPrice()->addFinalPrice()->addTaxPercents()->setVisibility(
            $this->productVisibility->getVisibleInCatalogIds()
        );

        if ($limit !== null) {
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
        } elseif ($collection instanceof \Magento\Framework\DataObject) {
            $items = $collection->getItems();
            foreach ($bundleCollection as $item) {
                $items[$item->getEntityId()] = $item;
            }
            $collection->setItems($items);
        }

        return $this;
    }
}
