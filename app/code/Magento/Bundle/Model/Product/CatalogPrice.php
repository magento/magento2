<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Bundle\Model\Product;

/**
 * Price model for external catalogs
 */
class CatalogPrice implements \Magento\Catalog\Model\Product\CatalogPriceInterface
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Catalog\Model\Product\CatalogPrice
     */
    protected $commonPriceModel;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry;

    /**
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Catalog\Model\Product\CatalogPrice $commonPriceModel
     * @param \Magento\Framework\Registry $coreRegistry
     */
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\Product\CatalogPrice $commonPriceModel,
        \Magento\Framework\Registry $coreRegistry
    ) {
        $this->storeManager = $storeManager;
        $this->commonPriceModel = $commonPriceModel;
        $this->coreRegistry = $coreRegistry;
    }

    /**
     * {@inheritdoc}
     */
    public function getCatalogPrice(
        \Magento\Catalog\Model\Product $product,
        \Magento\Store\Api\Data\StoreInterface $store = null,
        $inclTax = false
    ) {
        if ($store instanceof \Magento\Store\Api\Data\StoreInterface) {
            $currentStore = $this->storeManager->getStore();
            $this->storeManager->setCurrentStore($store->getId());
        }

        $this->coreRegistry->unregister('rule_data');
        $this->coreRegistry->register(
            'rule_data',
            new \Magento\Framework\DataObject(
                [
                    'store_id' => $product->getStoreId(),
                    'website_id' => $product->getWebsiteId(),
                    'customer_group_id' => $product->getCustomerGroupId(),
                ]
            )
        );

        $minPrice = $product->getPriceModel()->getTotalPrices($product, 'min', $inclTax);

        if ($store instanceof \Magento\Store\Api\Data\StoreInterface) {
            $this->storeManager->setCurrentStore($currentStore->getId());
        }
        return $minPrice;
    }

    /**
     * Regular catalog price not applicable for bundle product
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return null
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getCatalogRegularPrice(\Magento\Catalog\Model\Product $product)
    {
        return null;
    }
}
