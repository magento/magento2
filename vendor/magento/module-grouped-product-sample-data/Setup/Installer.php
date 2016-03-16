<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GroupedProductSampleData\Setup;

use Magento\Framework\Setup;

class Installer implements Setup\SampleData\InstallerInterface
{
    /**
     * Setup class for grouped products
     *
     * @var \Magento\GroupedProductSampleData\Model\Product
     */
    protected $groupedProduct;

    /**
     * @param \Magento\GroupedProductSampleData\Model\Product $groupedProduct
     */
    public function __construct(\Magento\GroupedProductSampleData\Model\Product $groupedProduct) {
        $this->groupedProduct = $groupedProduct;
    }

    /**
     * {@inheritdoc}
     */
    public function install()
    {
        $this->groupedProduct->install(
            ['Magento_GroupedProductSampleData::fixtures/yoga_grouped.csv'],
            ['Magento_GroupedProductSampleData::fixtures/images_yoga_grouped.csv']
        );
    }
}