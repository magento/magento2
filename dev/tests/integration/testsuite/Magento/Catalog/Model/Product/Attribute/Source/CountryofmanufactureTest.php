<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Product\Attribute\Source;

use Magento\TestFramework\Helper\CacheCleaner;

class CountryofmanufactureTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Catalog\Model\Product\Attribute\Source\Countryofmanufacture
     */
    private $model;

    protected function setUp()
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->model = $objectManager->create(
            \Magento\Catalog\Model\Product\Attribute\Source\Countryofmanufacture::class
        );
    }

    public function testGetAllOptions()
    {
        CacheCleaner::cleanAll();
        $allOptions = $this->model->getAllOptions();
        $cachedAllOptions = $this->model->getAllOptions();
        $this->assertEquals($allOptions, $cachedAllOptions);
    }
}
