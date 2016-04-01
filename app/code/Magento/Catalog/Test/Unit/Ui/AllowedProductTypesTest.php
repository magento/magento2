<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Ui;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Catalog\Ui\AllowedProductTypes;

class AllowedProductTypesTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ObjectManagerHelper
     */
    protected $objectManagerHelper;

    /**
     * @return void
     */
    public function testGetAllowedProductTypesWithoutConstructorArguments()
    {
        /** @var AllowedProductTypes $testedClass */
        $testedClass = (new ObjectManagerHelper($this))->getObject(AllowedProductTypes::class);
        $this->assertSame([], $testedClass->getAllowedProductTypes());
    }

    /**
     * @return void
     */
    public function testGetAllowedProductTypes()
    {
        $productTypes = ['simple', 'virtual'];
        $testedClass = (new ObjectManagerHelper($this))->getObject(
            AllowedProductTypes::class,
            ['productTypes' => $productTypes]
        );

        $this->assertSame($productTypes, $testedClass->getAllowedProductTypes());
    }
}