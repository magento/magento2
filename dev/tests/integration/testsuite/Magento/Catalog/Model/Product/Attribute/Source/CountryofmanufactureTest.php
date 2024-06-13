<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
namespace Magento\Catalog\Model\Product\Attribute\Source;

use Magento\TestFramework\Helper\CacheCleaner;

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
}
