<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\BundleImportExport\Test\Fixture\Import;

use Magento\Mtf\Fixture\FixtureInterface;
use Magento\Mtf\Fixture\FixtureFactory;

/**
 * Bundle product class file.
 */
class File
{
    /**
     * Prepare bundle product data.
     *
     * @param FixtureInterface $product
     * @param FixtureFactory $fixtureFactory
     * @return array
     */
    public function getData(FixtureInterface $product, FixtureFactory $fixtureFactory)
    {
        $newProduct = $fixtureFactory->createByCode('catalogProductSimple', ['dataset' => 'default']);
        $newProduct->persist();
        $newProductData = $newProduct->getData();
        $productData = $product->getData();

        $productData['bundle_attribute_sku'] = $newProductData['sku'];
        $productData['bundle_attribute_name'] = $newProductData['name'];
        $productData['bundle_attribute_url_key'] = $newProductData['url_key'];

        return $productData;
    }
}
