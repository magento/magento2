<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GroupedProduct\Model\Product;

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
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Catalog\Model\Product\CatalogPrice $commonPriceModel
     */
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\Product\CatalogPrice $commonPriceModel
    ) {
        $this->storeManager = $storeManager;
        $this->commonPriceModel = $commonPriceModel;
    }

    /**
     * Minimal price for "regular" user
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param null|\Magento\Store\Model\Store $store Store view
     * @param bool $inclTax
     * @return null|float
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getCatalogPrice(\Magento\Catalog\Model\Product $product, $store = null, $inclTax = false)
    {
        // Workaround to avoid loading stock status by admin's website
        if ($store instanceof \Magento\Store\Model\Store) {
            $oldStore = $this->storeManager->getStore();
            $this->storeManager->setCurrentStore($store);
        }
        $subProducts = $product->getTypeInstance()->getAssociatedProducts($product);
        if ($store instanceof \Magento\Store\Model\Store) {
            $this->storeManager->setCurrentStore($oldStore);
        }
        if (!count($subProducts)) {
            return null;
        }
        $minPrice = null;
        foreach ($subProducts as $subProduct) {
            $subProduct->setWebsiteId($product->getWebsiteId())->setCustomerGroupId($product->getCustomerGroupId());
            if ($subProduct->isSalable()) {
                if ($this->commonPriceModel->getCatalogPrice($subProduct) < $minPrice || $minPrice === null) {
                    $minPrice = $this->commonPriceModel->getCatalogPrice($subProduct);
                    $product->setTaxClassId($subProduct->getTaxClassId());
                }
            }
        }
        return $minPrice;
    }

    /**
     * Regular catalog price not applicable for grouped product
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
