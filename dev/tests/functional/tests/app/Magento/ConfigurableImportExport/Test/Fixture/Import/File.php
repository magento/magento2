<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableImportExport\Test\Fixture\Import;

use Magento\Mtf\Fixture\FixtureInterface;
use Magento\Mtf\Fixture\FixtureFactory;

/**
 * Configurable product class file.
 */
class File
{
    /**
     * Prepare configurable product data.
     *
     * @param FixtureInterface $product
     * @param FixtureFactory $fixtureFactory
     * @return array
     */
    public function getData(FixtureInterface $product, FixtureFactory $fixtureFactory)
    {
        $newProduct = $fixtureFactory->createByCode('configurableProduct', ['dataset' => 'with_one_attribute']);
        $newProduct->persist();
        $newProductData = $newProduct->getData();
        $newAttributeData = $newProductData['configurable_attributes_data']['matrix']['attribute_key_0:option_key_0'];
        $productData = $product->getData();

        $productData['configurable_attribute_sku'] = $newAttributeData['sku'];
        $productData['configurable_attribute_name'] = $newAttributeData['name'];
        $productData['configurable_attribute_url_key'] = str_replace('_', '-', $newAttributeData['sku']);
        $productData['configurable_additional_attributes'] =
            $newProductData['configurable_attributes_data']['attributes_data']['attribute_key_0']['frontend_label'];

        return $productData;
    }
}
