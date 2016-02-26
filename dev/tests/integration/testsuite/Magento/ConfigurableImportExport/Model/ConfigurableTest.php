<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableImportExport\Model;

use Magento\CatalogImportExport\Model\AbstractProductExportImportTestCase;

class ConfigurableTest extends AbstractProductExportImportTestCase
{
    public function exportImportDataProvider()
    {
        return [
            'configurable-product' => [
                [
                    'Magento/ConfigurableProduct/_files/product_configurable.php'
                ],
                [
                    'configurable',
                ],
                ['_cache_instance_products', '_cache_instance_configurable_attributes'],
                [
                    'Magento/ConfigurableProduct/_files/product_configurable_rollback.php'
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

        sort($origAssociatedProductSkus);
        sort($newAssociatedProductSkus);

        $this->assertEquals($origAssociatedProductSkus, $newAssociatedProductSkus);

        $origProductExtensionAttributes = $origProduct->getExtensionAttributes();
        $newProductExtensionAttributes = $newProduct->getExtensionAttributes();

        $this->assertEquals(
            count($origProductExtensionAttributes->getConfigurableProductLinks()),
            count($newProductExtensionAttributes->getConfigurableProductLinks())
        );

        $origConfigurableProductOptions = $origProductExtensionAttributes->getConfigurableProductOptions();
        $newConfigurableProductOptions = $newProductExtensionAttributes->getConfigurableProductOptions();

        $this->assertEquals(count($origConfigurableProductOptions), count($newConfigurableProductOptions));

        $origConfigurableProductOptionsToCompare = [];
        foreach ($origConfigurableProductOptions as $origConfigurableProductOption) {
            foreach ($origConfigurableProductOption->getOptions() as $optionValue) {
                $origConfigurableProductOptionsToCompare[$origConfigurableProductOption->getLabel()][]
                    = $optionValue['label'];
            }
        }

        $newConfigurableProductOptionsToCompare = [];
        foreach ($newConfigurableProductOptions as $newConfigurableProductOption) {
            foreach ($newConfigurableProductOption->getOptions() as $optionValue) {
                $newConfigurableProductOptionsToCompare[$newConfigurableProductOption->getLabel()][]
                    = $optionValue['label'];
            }
        }

        $this->assertEquals(
            count($origConfigurableProductOptionsToCompare),
            count($newConfigurableProductOptionsToCompare)
        );

        foreach ($origConfigurableProductOptionsToCompare as $key => $origOptionValues) {
            $newOptionValues = $newConfigurableProductOptionsToCompare[$key];
            sort($origOptionValues);
            sort($newOptionValues);
            $this->assertEquals($origOptionValues, $newOptionValues);
        }
    }
}
