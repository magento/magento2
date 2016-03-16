<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableSampleData\Setup;

use Magento\Framework\Setup;

class Installer implements Setup\SampleData\InstallerInterface
{
    /**
     * @var \Magento\CatalogSampleData\Model\Attribute
     */
    private $attribute;

    /**
     * @var \Magento\CatalogSampleData\Model\Category
     */
    private $category;

    /**
     * @var \Magento\ConfigurableSampleData\Model\Product
     */
    private $configurableProduct;

    /**
     * @var \Magento\ProductLinksSampleData\Model\ProductLink
     */
    protected $productLinkSetup;

    /**
     * @param \Magento\CatalogSampleData\Model\Attribute $attribute
     * @param \Magento\CatalogSampleData\Model\Category $category
     * @param \Magento\ConfigurableSampleData\Model\Product $configurableProduct
     * @param \Magento\ProductLinksSampleData\Model\ProductLink $productLinkSetup
     */
    public function __construct(
        \Magento\CatalogSampleData\Model\Attribute $attribute,
        \Magento\CatalogSampleData\Model\Category $category,
        \Magento\ConfigurableSampleData\Model\Product $configurableProduct,
        \Magento\ProductLinksSampleData\Model\ProductLink $productLinkSetup
    ) {
        $this->attribute = $attribute;
        $this->category = $category;
        $this->configurableProduct = $configurableProduct;
        $this->productLinkSetup = $productLinkSetup;
    }

    /**
     * {@inheritdoc}
     */
    public function install()
    {
        $this->attribute->install(['Magento_ConfigurableSampleData::fixtures/attributes.csv']);
        $this->category->install(['Magento_ConfigurableSampleData::fixtures/categories.csv']);
        $this->configurableProduct->install();
        $this->productLinkSetup->install(
            ['Magento_ConfigurableSampleData::fixtures/Links/related.csv'],
            ['Magento_ConfigurableSampleData::fixtures/Links/upsell.csv'],
            ['Magento_ConfigurableSampleData::fixtures/Links/crossell.csv']
        );
    }
}
