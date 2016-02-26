<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\BundleImportExport\Model;

use Magento\CatalogImportExport\Model\AbstractProductExportImportTestCase;

class BundleTest extends AbstractProductExportImportTestCase
{
    public function exportImportDataProvider()
    {
        return [
            // @todo uncomment after MAGETWO-49677 resolved
            /*
            'bundle-product' => [
                [
                    'Magento/Bundle/_files/product.php'
                ],
                [
                    'bundle-product',
                ]
            ],
            */
            'bundle-product-multi-options' => [
                [
                    'Magento/Bundle/_files/product_with_multiple_options.php'
                ],
                [
                    'bundle-product',
                ]
            ],
            // @todo uncomment after MAGETWO-49677 resolved
            /*
            'bundle-product-tier-pricing' => [
                [
                    'Magento/Bundle/_files/product_with_tier_pricing.php'
                ],
                [
                    'bundle-product',
                ]
            ]
            */
        ];
    }

    /**
     * @param \Magento\Catalog\Model\Product $origProduct
     * @param \Magento\Catalog\Model\Product $newProduct
     */
    protected function assertEqualsSpecificAttributes($origProduct, $newProduct)
    {
        $origBundleProductOptions = $origProduct->getExtensionAttributes()->getBundleProductOptions();
        $newBundleProductOptions = $newProduct->getExtensionAttributes()->getBundleProductOptions();

        $this->assertEquals(count($origBundleProductOptions), count($newBundleProductOptions));

        $origBundleProductOptionsToCompare = [];
        foreach ($origBundleProductOptions as $origBundleProductOption) {
            $origBundleProductOptionsToCompare[$origBundleProductOption->getTitle()]['type']
                = $origBundleProductOption->getType();
            foreach ($origBundleProductOption->getProductLinks() as $productLink) {
                $origBundleProductOptionsToCompare[$origBundleProductOption->getTitle()]['product_links'][]
                    = $productLink->getSku();
            }
        }

        $newBundleProductOptionsToCompare = [];
        foreach ($newBundleProductOptions as $newBundleProductOption) {
            $newBundleProductOptionsToCompare[$newBundleProductOption->getTitle()]['type']
                = $newBundleProductOption->getType();
            foreach ($newBundleProductOption->getProductLinks() as $productLink) {
                $newBundleProductOptionsToCompare[$newBundleProductOption->getTitle()]['product_links'][]
                    = $productLink->getSku();
            }
        }

        $this->assertEquals(count($origBundleProductOptions), count($newBundleProductOptions));

        foreach ($origBundleProductOptionsToCompare as $key => $origBundleProductOption) {
            $this->assertEquals(
                $origBundleProductOption['type'],
                $newBundleProductOptionsToCompare[$key]['type']
            );

            $origProductLinks = $origBundleProductOption['product_links'];
            $newProductLinks = $newBundleProductOptionsToCompare[$key]['product_links'];

            sort($origProductLinks);
            sort($newProductLinks);

            $this->assertEquals($origProductLinks, $newProductLinks);
        }
    }
}
