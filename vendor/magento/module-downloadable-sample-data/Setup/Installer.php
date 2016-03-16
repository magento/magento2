<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\DownloadableSampleData\Setup;

use Magento\Framework\Setup;

class Installer implements Setup\SampleData\InstallerInterface
{
    /**
     * Setup class for products
     *
     * @var \Magento\DownloadableSampleData\Model\Product
     */
    protected $productSetup;

    /**
     * @param \Magento\CatalogSampleData\Model\Category $category
     * @param \Magento\CatalogSampleData\Model\Attribute $attribute
     * @param \Magento\DownloadableSampleData\Model\Product $product
     */
    public function __construct(
        \Magento\CatalogSampleData\Model\Category $category,
        \Magento\CatalogSampleData\Model\Attribute $attribute,
        \Magento\DownloadableSampleData\Model\Product $product
    ) {
        $this->category = $category;
        $this->attribute = $attribute;
        $this->downloadableProduct = $product;
    }

    /**
     * {@inheritdoc}
     */
    public function install()
    {
        $this->attribute->install(['Magento_DownloadableSampleData::fixtures/attributes.csv']);
        $this->category->install(['Magento_DownloadableSampleData::fixtures/categories.csv']);
        $this->downloadableProduct->install(
            ['Magento_DownloadableSampleData::fixtures/products_training_video_download.csv'],
            ['Magento_DownloadableSampleData::fixtures/images_products_training_video.csv'],
            ['Magento_DownloadableSampleData::fixtures/downloadable_data_training_video_download.csv']
        );
    }
}