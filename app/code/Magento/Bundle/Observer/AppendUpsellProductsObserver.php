<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Bundle\Observer;

use Magento\Bundle\Helper\Data;
use Magento\Bundle\Model\ResourceModel\Selection;
use Magento\Catalog\Model\Config;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Catalog\Model\ResourceModel\Product\Link\Product\Collection as ProductLinkCollection;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Store\Model\ScopeInterface;

/**
 * Class adds bundle products into up-sell products collection
 */
class AppendUpsellProductsObserver implements ObserverInterface
{
    public const XML_CONFIG_PATH_UPSELL_ADD_BUNDLES = 'catalog/upsells/add_bundles';

    /**
     * Bundle data
     *
     * @var Data
     */
    protected $bundleData;

    /**
     * @var Selection
     */
    protected $bundleSelection;

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var Visibility
     */
    protected $productVisibility;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * AppendUpsellProductsObserver constructor.
     * @param Data $bundleData
     * @param Visibility $productVisibility
     * @param Config $config
     * @param Selection $bundleSelection
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        Data $bundleData,
        Visibility $productVisibility,
        Config $config,
        Selection $bundleSelection,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->bundleData = $bundleData;
        $this->productVisibility = $productVisibility;
        $this->config = $config;
        $this->bundleSelection = $bundleSelection;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Append bundles in upsell list for current product
     *
     * @param Observer $observer
     * @return $this
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @throws LocalizedException
     */
    public function execute(Observer $observer)
    {
        if (!$this->scopeConfig->isSetFlag(
            self::XML_CONFIG_PATH_UPSELL_ADD_BUNDLES,
            ScopeInterface::SCOPE_STORE
        )) {
            return $this;
        }

        /* @var $product Product */
        $product = $observer->getEvent()->getProduct();

        /**
         * Check is current product type is allowed for bundle selection product type
         */
        if (!in_array($product->getTypeId(), $this->bundleData->getAllowedSelectionTypes())) {
            return $this;
        }

        /* @var $collection ProductLinkCollection */
        $collection = $observer->getEvent()->getCollection();
        $limit = $observer->getEvent()->getLimit();
        if (is_array($limit)) {
            if (isset($limit['upsell'])) {
                $limit = $limit['upsell'];
            } else {
                $limit = 0;
            }
        }

        /* @var $resource Selection */
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

        /* @var $bundleCollection Collection */
        $bundleCollection = $product->getCollection();
        $bundleCollection->addAttributeToSelect(
            $this->config->getProductAttributes()
        );
        $bundleCollection->addStoreFilter();
        $bundleCollection->addMinimalPrice();
        $bundleCollection->addFinalPrice();
        $bundleCollection->addTaxPercents();
        $bundleCollection->setVisibility(
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
