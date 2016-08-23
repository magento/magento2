<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogImportExport\Test\Unit\Model\Import\Product\Validator;

use Magento\CatalogImportExport\Model\Import\Product;
use Magento\CatalogImportExport\Model\Import\Product\Validator\Quantity;

/**
 * Class QuantityTest
 */
class QuantityTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Quantity
     */
    private $quantity;

    protected function setUp()
    {
        $this->quantity = new Quantity();

        $contextStub = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->getMock();
        $contextStub->method('retrieveMessageTemplate')->willReturn(null);
        $this->quantity->init($contextStub);
    }

    /**
     * @param bool $expectedResult
     * @param array $value
     * @dataProvider isValidDataProvider
     */
    public function testIsValid($expectedResult, $value)
    {
        $result = $this->quantity->isValid($value);
        $this->assertEquals($expectedResult, $result);
    }

    /**
     * @return array
     */
    public function isValidDataProvider()
    {
        return [
            [true, ['qty' => 0]],
            [true, ['qty' => 1]],
            [true, ['qty' => 5]],
            [true, ['qty' => -1]],
            [true, ['qty' => -10]],
            [true, ['qty' => '']],
            [false, ['qty' => 'abc']],
            [false, ['qty' => true]],
        ];
    }
}
