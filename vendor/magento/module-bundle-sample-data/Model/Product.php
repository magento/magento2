<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\BundleSampleData\Model;

use Magento\Framework\Setup\SampleData\Context as SampleDataContext;

/**
 * Setup bundle product
 */
class Product extends \Magento\CatalogSampleData\Model\Product
{
    /**
     * @var string
     */
    protected $productType = \Magento\Catalog\Model\Product\Type::TYPE_BUNDLE;

    /**
     * Product constructor.
     * @param SampleDataContext $sampleDataContext
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Magento\Catalog\Model\ConfigFactory $catalogConfig
     * @param Product\Converter $converter
     * @param \Magento\CatalogSampleData\Model\Product\Gallery $gallery
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Eav\Model\Config $eavConfig
     */
    public function __construct(
        SampleDataContext $sampleDataContext,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Catalog\Model\ConfigFactory $catalogConfig,
        \Magento\BundleSampleData\Model\Product\Converter $converter,
        \Magento\CatalogSampleData\Model\Product\Gallery $gallery,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Eav\Model\Config $eavConfig
    ) {
        $this->eavConfig = $eavConfig;
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

    /**
     * @inheritdoc
     */
    protected function prepareProduct($product, $data)
    {
        $product
            ->setCanSaveConfigurableAttributes(true)
            ->setCanSaveBundleSelections(true)
            ->setPriceType(0);

        return $this;
    }
}
