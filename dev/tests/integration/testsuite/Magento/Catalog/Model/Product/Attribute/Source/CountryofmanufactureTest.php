<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
namespace Magento\Catalog\Model\Product\Attribute\Source;

use Magento\Catalog\Model\Product;
use Magento\TestFramework\Helper\Bootstrap;

class CountryofmanufactureTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Catalog\Model\Product\Attribute\Source\Countryofmanufacture
     */
    private $model;

    protected function setUp(): void
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->model = $objectManager->create(
            \Magento\Catalog\Model\Product\Attribute\Source\Countryofmanufacture::class
        );
    }

    public function testGetAllOptions()
    {
        $allOptions = $this->model->getAllOptions();
        $cachedAllOptions = $this->model->getAllOptions();
        $this->assertEquals($allOptions, $cachedAllOptions);
    }

    public function testUnassignedCountryOfManufactureText(): void
    {
        $product = Bootstrap::getObjectManager()->create(Product::class);
        $attribute = $product->getResource()->getAttribute('country_of_manufacture');

        $this->assertNull($product->getData('country_of_manufacture'));
        $this->assertSame('', $product->getAttributeText('country_of_manufacture'));
        $this->assertSame('', $attribute->getFrontend()->getValue($product));
    }
}
