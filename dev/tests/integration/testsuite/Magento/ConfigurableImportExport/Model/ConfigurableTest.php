<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableImportExport\Model;

use Magento\CatalogImportExport\Model\AbstractProductExportImportTestCase;

class ConfigurableTest extends AbstractProductExportImportTestCase
{
    /**
     * @return array
     */
    public function exportImportDataProvider(): array
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
            ],
            'configurable-product-12345' => [
                [
                    'Magento/ConfigurableProduct/_files/product_configurable_12345.php'
                ],
                [
                    '12345',
                ],
                ['_cache_instance_products', '_cache_instance_configurable_attributes'],
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    protected function assertEqualsSpecificAttributes(
        \Magento\Catalog\Model\Product $expectedProduct,
        \Magento\Catalog\Model\Product $actualProduct
    ): void {
        /** @var \Magento\ConfigurableProduct\Model\Product\Type\Configurable $productType */
        $productType = $expectedProduct->getTypeInstance();
        $expectedAssociatedProducts = $productType->getUsedProductCollection($expectedProduct);
        $actualAssociatedProducts = iterator_to_array($productType->getUsedProductCollection($actualProduct));

        $expectedAssociatedProductSkus = [];
        $actualAssociatedProductSkus = [];
        foreach ($expectedAssociatedProducts as $i => $associatedProduct) {
            $expectedAssociatedProductSkus[] = $associatedProduct->getSku();
            $actualAssociatedProductSkus[] = $actualAssociatedProducts[$i]->getSku();
        }

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

    /**
     * @inheritdoc
     */
    protected function executeImportReplaceTest(
        $skus,
        $skippedAttributes,
        $usePagination = false,
        string $csvfile = null
    ) {
        $skippedAttributes = array_merge($skippedAttributes, ['_cache_instance_product_set_attributes']);
        parent::executeImportReplaceTest($skus, $skippedAttributes, $usePagination, $csvfile);
    }
}
