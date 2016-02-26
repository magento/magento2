<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GroupedImportExport\Model;

use Magento\CatalogImportExport\Model\AbstractProductExportImportTestCase;

class GroupedTest extends AbstractProductExportImportTestCase
{
    public function exportImportDataProvider()
    {
        return [
            'grouped-product' => [
                [
                    'Magento/GroupedProduct/_files/product_grouped.php'
                ],
                [
                    'grouped-product',
                ]
            ],
        ];
    }

    /**
     * @param \Magento\Catalog\Model\Product $origProduct
     * @param \Magento\Catalog\Model\Product $newProduct
     */
    protected function assertEqualsSpecificAttributes($origProduct, $newProduct)
    {
        $origAssociatedProducts = $origProduct->getTypeInstance()->getAssociatedProducts($origProduct);
        $newAssociatedProducts = $newProduct->getTypeInstance()->getAssociatedProducts($newProduct);

        $origAssociatedProductSkus = [];
        $newAssociatedProductSkus = [];
        $i = 0;
        foreach ($origAssociatedProducts as $associatedProduct) {
            $origAssociatedProductSkus[] = $associatedProduct->getSku();
            $newAssociatedProductSkus[] = $newAssociatedProducts[$i]->getSku();
            $i++;
        }

        $this->assertEquals($origAssociatedProductSkus, $newAssociatedProductSkus);
    }
}
