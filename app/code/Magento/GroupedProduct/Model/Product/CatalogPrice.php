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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\GroupedProduct\Model\Product;

/**
 * Price model for external catalogs
 */
class CatalogPrice implements \Magento\Catalog\Model\Product\CatalogPriceInterface
{
    /**
     * @var \Magento\Framework\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Catalog\Model\Product\CatalogPrice
     */
    protected $commonPriceModel;

    /**
     * @param \Magento\Framework\StoreManagerInterface $storeManager
     * @param \Magento\Catalog\Model\Product\CatalogPrice $commonPriceModel
     */
    public function __construct(
        \Magento\Framework\StoreManagerInterface $storeManager,
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
