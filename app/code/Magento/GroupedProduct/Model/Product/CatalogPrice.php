<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GroupedProduct\Model\Product;

/**
 * Price model for external catalogs
 * @since 2.0.0
 */
class CatalogPrice implements \Magento\Catalog\Model\Product\CatalogPriceInterface
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     * @since 2.0.0
     */
    protected $storeManager;

    /**
     * @var \Magento\Catalog\Model\Product\CatalogPrice
     * @since 2.0.0
     */
    protected $commonPriceModel;

    /**
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Catalog\Model\Product\CatalogPrice $commonPriceModel
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\Product\CatalogPrice $commonPriceModel
    ) {
        $this->storeManager = $storeManager;
        $this->commonPriceModel = $commonPriceModel;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @since 2.0.0
     */
    public function getCatalogPrice(
        \Magento\Catalog\Model\Product $product,
        \Magento\Store\Api\Data\StoreInterface $store = null,
        $inclTax = false
    ) {
        // Workaround to avoid loading stock status by admin's website
        if ($store instanceof \Magento\Store\Api\Data\StoreInterface) {
            $currentStore = $this->storeManager->getStore();
            $this->storeManager->setCurrentStore($store->getId());
        }
        $subProducts = $product->getTypeInstance()->getAssociatedProducts($product);
        if ($store instanceof \Magento\Store\Api\Data\StoreInterface) {
            $this->storeManager->setCurrentStore($currentStore->getId());
        }
        if (!$subProducts) {
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
     * @since 2.0.0
     */
    public function getCatalogRegularPrice(\Magento\Catalog\Model\Product $product)
    {
        return null;
    }
}
