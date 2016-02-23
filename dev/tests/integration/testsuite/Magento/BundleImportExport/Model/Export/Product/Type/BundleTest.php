<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\BundleImportExport\Model\Export\Product\Type;

use Magento\CatalogImportExport\Model\Export\AbstractProductExportTestCase;

class BundleTest extends AbstractProductExportTestCase
{
    public function exportDataProvider()
    {
        return [
            'bundle-product' => [
                'Magento/Bundle/_files/product.php',
                [
                    'bundle-product',
                ]
            ],
            'bundle-product-multi-options' => [
                'Magento/Bundle/_files/product_with_multiple_options.php',
                [
                    'bundle-product',
                ]
            ],
            'bundle-product-tie-pricing' => [
                'Magento/Bundle/_files/product_with_tier_pricing.php',
                [
                    'bundle-product',
                ]
            ],
        ];
    }
}
