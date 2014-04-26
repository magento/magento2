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
namespace Magento\Catalog\Model\Product;

use Magento\Framework\ObjectManager;

/**
 * Price model for external catalogs
 */
class CatalogPrice implements CatalogPriceInterface
{
    /**
     * @var CatalogPriceFactory
     */
    protected $priceModelFactory;

    /**
     * @var array catalog price models for different product types
     */
    protected $priceModelPool;

    /**
     *
     * @param CatalogPriceFactory $priceModelFactory
     * @param array $priceModelPool
     */
    public function __construct(CatalogPriceFactory $priceModelFactory, array $priceModelPool)
    {
        $this->priceModelFactory = $priceModelFactory;
        $this->priceModelPool = $priceModelPool;
    }

    /**
     * Minimal price for "regular" user
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param null|\Magento\Store\Model\Store $store Store view
     * @param bool $inclTax
     * @throws \UnexpectedValueException
     * @return null|float
     */
    public function getCatalogPrice(\Magento\Catalog\Model\Product $product, $store = null, $inclTax = false)
    {
        if (array_key_exists($product->getTypeId(), $this->priceModelPool)) {
            $catalogPriceModel = $this->priceModelFactory->create($this->priceModelPool[$product->getTypeId()]);
            return $catalogPriceModel->getCatalogPrice($product, $store, $inclTax);
        }

        return $product->getFinalPrice();
    }

    /**
     * Regular catalog price
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return float
     * @throws \UnexpectedValueException
     */
    public function getCatalogRegularPrice(\Magento\Catalog\Model\Product $product)
    {
        if (array_key_exists($product->getTypeId(), $this->priceModelPool)) {
            $catalogPriceModel = $this->priceModelFactory->create($this->priceModelPool[$product->getTypeId()]);
            return $catalogPriceModel->getCatalogRegularPrice($product);
        }

        return $product->getPrice();
    }
}
