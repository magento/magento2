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
namespace Magento\Bundle\Model\Product;

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
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry;

    /**
     * @param \Magento\Framework\StoreManagerInterface $storeManager
     * @param \Magento\Catalog\Model\Product\CatalogPrice $commonPriceModel
     * @param \Magento\Framework\Registry $coreRegistry
     */
    public function __construct(
        \Magento\Framework\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\Product\CatalogPrice $commonPriceModel,
        \Magento\Framework\Registry $coreRegistry
    ) {
        $this->storeManager = $storeManager;
        $this->commonPriceModel = $commonPriceModel;
        $this->coreRegistry = $coreRegistry;
    }

    /**
     * Minimal price for "regular" user
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param null|\Magento\Store\Model\Store $store Store view
     * @param bool $inclTax
     * @return null|float
     */
    public function getCatalogPrice(\Magento\Catalog\Model\Product $product, $store = null, $inclTax = false)
    {
        if ($store instanceof \Magento\Store\Model\Store) {
            $oldStore = $this->storeManager->getStore();
            $this->storeManager->setCurrentStore($store);
        }

        $this->coreRegistry->unregister('rule_data');
        $this->coreRegistry->register(
            'rule_data',
            new \Magento\Framework\Object(
                array(
                    'store_id' => $product->getStoreId(),
                    'website_id' => $product->getWebsiteId(),
                    'customer_group_id' => $product->getCustomerGroupId()
                )
            )
        );

        $minPrice = $product->getPriceModel()->getTotalPrices($product, 'min', $inclTax);

        if ($store instanceof \Magento\Store\Model\Store) {
            $this->storeManager->setCurrentStore($oldStore);
        }
        return $minPrice;
    }

    /**
     * Regular catalog price not applicable for bundle product
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return null
     */
    public function getCatalogRegularPrice(\Magento\Catalog\Model\Product $product)
    {
        return null;
    }
}
