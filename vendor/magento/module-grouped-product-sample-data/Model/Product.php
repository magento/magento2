<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GroupedProductSampleData\Model;

use Magento\Framework\Setup\SampleData\Context as SampleDataContext;

/**
 * Setup grouped product
 */
class Product extends \Magento\CatalogSampleData\Model\Product
{
    /**
     * @var string
     */
    protected $productType = \Magento\GroupedProduct\Model\Product\Type\Grouped::TYPE_CODE;

    /**
     * Product constructor.
     * @param SampleDataContext $sampleDataContext
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Magento\Catalog\Model\ConfigFactory $catalogConfig
     * @param \Magento\GroupedProductSampleData\Model\Product\Converter $converter
     * @param \Magento\Framework\Setup\SampleData\FixtureManager $fixtureManager
     * @param \Magento\CatalogSampleData\Model\Product\Gallery $gallery
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Eav\Model\Config $eavConfig
     */
    public function __construct(
        SampleDataContext $sampleDataContext,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Catalog\Model\ConfigFactory $catalogConfig,
        \Magento\GroupedProductSampleData\Model\Product\Converter $converter,
        \Magento\Framework\Setup\SampleData\FixtureManager $fixtureManager,
        \Magento\CatalogSampleData\Model\Product\Gallery $gallery,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Eav\Model\Config $eavConfig
    ) {
        parent::__construct(
            $sampleDataContext,
            $productFactory,
            $catalogConfig,
            $converter,
            $gallery,
            $storeManager,
            $eavConfig
        );
    }
}
