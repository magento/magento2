<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
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
            ],
        ];
    }

    /**
     * @param \Magento\Catalog\Model\Product $expectedProduct
     * @param \Magento\Catalog\Model\Product $actualProduct
     */
    protected function assertEqualsSpecificAttributes($expectedProduct, $actualProduct)
    {
        /** @var \Magento\ConfigurableProduct\Model\Product\Type\Configurable $prooductType */
        $prooductType = $expectedProduct->getTypeInstance();
        $expectedAssociatedProducts = $prooductType->getUsedProductCollection($expectedProduct);
        $actualAssociatedProducts = iterator_to_array($prooductType->getUsedProductCollection($actualProduct));

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

    public function importReplaceDataProvider()
    {
        $data = $this->exportImportDataProvider();
        foreach ($data as $key => $value) {
            $data[$key][2] = array_merge($value[2], ['_cache_instance_product_set_attributes']);
        }
        return $data;
    }

    /**
     * @magentoAppArea adminhtml
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     *
     * @param array $fixtures
     * @param string[] $skus
     * @param string[] $skippedAttributes
     * @dataProvider importReplaceDataProvider
     */
    public function testImportReplace($fixtures, $skus, $skippedAttributes = [])
    {
        parent::testImportReplace($fixtures, $skus, $skippedAttributes);
    }
}
