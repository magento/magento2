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
 * @category   Magento
 * @package    Magento_GoogleShopping
 * @copyright  Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Price helper
 * This class is workaround for problem of getting appropriate price for
 * some types of products: bundle, grouped, gift cards; abstract price model
 * doesn't give access to such information
 *
 * @category   Magento
 * @package    Magento_GoogleShopping
 * @author     Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\GoogleShopping\Helper;

class Price
{
    /**
     * Core registry
     *
     * @var \Magento\Core\Model\Registry
     */
    protected $_coreRegistry = null;

    /**
     * Store manager
     *
     * @var \Magento\Core\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @param \Magento\Core\Model\Registry $coreRegistry
     * @param \Magento\Core\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        \Magento\Core\Model\Registry $coreRegistry,
        \Magento\Core\Model\StoreManagerInterface $storeManager
    ) {
        $this->_coreRegistry = $coreRegistry;
        $this->_storeManager = $storeManager;
    }

    /**
     * Tries to return price that looks like price in catalog
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param null|\Magento\Core\Model\Store $store Store view
     * @return null|float Price
     */
    public function getCatalogPrice(\Magento\Catalog\Model\Product $product, $store = null, $inclTax = null)
    {
        switch ($product->getTypeId()) {
            case \Magento\Catalog\Model\Product\Type::TYPE_GROUPED:
                // Workaround to avoid loading stock status by admin's website
                if ($store instanceof \Magento\Core\Model\Store) {
                    $oldStore = $this->_storeManager->getStore();
                    $this->_storeManager->setCurrentStore($store);
                }
                $subProducts = $product->getTypeInstance()->getAssociatedProducts($product);
                if ($store instanceof \Magento\Core\Model\Store) {
                    $this->_storeManager->setCurrentStore($oldStore);
                }
                if (!count($subProducts)) {
                    return null;
                }
                $minPrice = null;
                foreach ($subProducts as $subProduct) {
                    $subProduct->setWebsiteId($product->getWebsiteId())
                        ->setCustomerGroupId($product->getCustomerGroupId());
                    if ($subProduct->isSalable()) {
                        if ($this->getCatalogPrice($subProduct) < $minPrice || $minPrice === null) {
                            $minPrice = $this->getCatalogPrice($subProduct);
                            $product->setTaxClassId($subProduct->getTaxClassId());
                        }
                    }
                }
                return $minPrice;

            case \Magento\Catalog\Model\Product\Type::TYPE_BUNDLE:
                if ($store instanceof \Magento\Core\Model\Store) {
                    $oldStore = $this->_storeManager->getStore();
                    $this->_storeManager->setCurrentStore($store);
                }

                $this->_coreRegistry->unregister('rule_data');
                $this->_coreRegistry->register('rule_data', new \Magento\Object(array(
                    'store_id'          => $product->getStoreId(),
                    'website_id'        => $product->getWebsiteId(),
                    'customer_group_id' => $product->getCustomerGroupId())));

                $minPrice = $product->getPriceModel()->getTotalPrices($product, 'min', $inclTax);

                if ($store instanceof \Magento\Core\Model\Store) {
                    $this->_storeManager->setCurrentStore($oldStore);
                }
                return $minPrice;

            case 'giftcard':
                return $product->getPriceModel()->getMinAmount($product);

            default:
                return $product->getFinalPrice();
        }
    }

    /**
     * Tries calculate price without discount; if can't returns nul
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param mixed $store
     */
    public function getCatalogRegularPrice(\Magento\Catalog\Model\Product $product, $store = null)
    {
        switch ($product->getTypeId()) {
            case \Magento\Catalog\Model\Product\Type::TYPE_GROUPED:
            case \Magento\Catalog\Model\Product\Type::TYPE_BUNDLE:
            case 'giftcard':
                return null;

            default:
                return $product->getPrice();
        }
    }
}
