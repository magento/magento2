<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogImportExport\Test\Unit\Model\Import\Product\Validator;

use Magento\CatalogImportExport\Model\Import\Product;
use Magento\CatalogImportExport\Model\Import\Product\Validator\Quantity;
use Magento\ImportExport\Model\Import;

/**
 * Class QuantityTest
 */
class QuantityTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Quantity
     */
    private $quantity;

    protected function setUp(): void
    {
        $this->quantity = new Quantity();

        $contextStub = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->getMock();
        $contextStub->expects($this->any())
            ->method('getEmptyAttributeValueConstant')
            ->willReturn(Import::DEFAULT_EMPTY_ATTRIBUTE_VALUE_CONSTANT);

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
            [false, ['qty' => true]],
            [true, ['qty' => Import::DEFAULT_EMPTY_ATTRIBUTE_VALUE_CONSTANT]],
            [false, ['qty' => '__EMPTY__VALUE__TEST__']],
        ];
    }
}
