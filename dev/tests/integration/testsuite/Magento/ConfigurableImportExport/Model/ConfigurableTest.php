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
     * @param \Magento\Catalog\Model\Product $expectedProduct
     * @param \Magento\Catalog\Model\Product $actualProduct
     */
    protected function assertEqualsSpecificAttributes($expectedProduct, $actualProduct)
    {
        $expectedAssociatedProducts = $expectedProduct->getTypeInstance()->getAssociatedProducts($expectedProduct);
        $actualAssociatedProducts = $actualProduct->getTypeInstance()->getAssociatedProducts($actualProduct);

        $expectedAssociatedProductSkus = [];
        $actualAssociatedProductSkus = [];
        $i = 0;
        foreach ($expectedAssociatedProducts as $associatedProduct) {
            $expectedAssociatedProductSkus[] = $associatedProduct->getSku();
            $actualAssociatedProductSkus[] = $actualAssociatedProducts[$i]->getSku();
            $i++;
        }

        sort($expectedAssociatedProductSkus);
        sort($actualAssociatedProductSkus);

        $this->assertEquals($expectedAssociatedProductSkus, $actualAssociatedProductSkus);

        $expectedProductExtensionAttributes = $expectedProduct->getExtensionAttributes();
        $actualProductExtensionAttributes = $actualProduct->getExtensionAttributes();

        $this->assertEquals(
            count($expectedProductExtensionAttributes->getConfigurableProductLinks()),
            count($actualProductExtensionAttributes->getConfigurableProductLinks())
        );

        $expectedConfigurableProductOptions = $expectedProductExtensionAttributes->getConfigurableProductOptions();
        $actualConfigurableProductOptions = $actualProductExtensionAttributes->getConfigurableProductOptions();

        $this->assertEquals(count($expectedConfigurableProductOptions), count($actualConfigurableProductOptions));

        $expectedConfigurableProductOptionsToCompare = [];
        foreach ($expectedConfigurableProductOptions as $expectedConfigurableProductOption) {
            foreach ($expectedConfigurableProductOption->getOptions() as $optionValue) {
                $expectedConfigurableProductOptionsToCompare[$expectedConfigurableProductOption->getLabel()][]
                    = $optionValue['label'];
            }
        }

        $actualConfigurableProductOptionsToCompare = [];
        foreach ($actualConfigurableProductOptions as $actualConfigurableProductOption) {
            foreach ($actualConfigurableProductOption->getOptions() as $optionValue) {
                $actualConfigurableProductOptionsToCompare[$actualConfigurableProductOption->getLabel()][]
                    = $optionValue['label'];
            }
        }

        $this->assertEquals(
            count($expectedConfigurableProductOptionsToCompare),
            count($actualConfigurableProductOptionsToCompare)
        );

        foreach ($expectedConfigurableProductOptionsToCompare as $key => $expectedOptionValues) {
            $actualOptionValues = $actualConfigurableProductOptionsToCompare[$key];
            sort($expectedOptionValues);
            sort($actualOptionValues);
            $this->assertEquals($expectedOptionValues, $actualOptionValues);
        }
    }
}
