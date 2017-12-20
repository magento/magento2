<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogImportExport\Test\Unit\Model\Import\Product\Validator;

use Magento\CatalogImportExport\Model\Import\Product;
use Magento\CatalogImportExport\Model\Import\Product\Type\AbstractType;
use Magento\CatalogImportExport\Model\Import\Product\Validator\Weight;

class WeightTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Weight
     */
    private $weight;

    protected function setUp()
    {
        $this->weight = new Weight();

        $contextStub = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->getMock();
        $contextStub->method('retrieveMessageTemplate')->willReturn(null);
        $this->weight->init($contextStub);
    }

    /**
     * @param bool $expectedResult
     * @param array $value
     * @dataProvider isValidDataProvider
     */
    public function testIsValid($expectedResult, $value)
    {
        $result = $this->weight->isValid($value);
        $this->assertEquals($expectedResult, $result);
    }

    /**
     * @return array
     */
    public function isValidDataProvider()
    {
        return [
            [true, ['weight' => 0]],
            [true, ['weight' => 1]],
            [true, ['weight' => 5]],
            [false, ['weight' => -1]],
            [false, ['weight' => -10]],
            [true, ['weight' => '']],
            [false, ['weight' => 'abc']],
            [false, ['weight' => true]],
            [false, ['weight' => true]],
            [true, ['weight' => AbstractType::EMPTY_VALUE]],
        ];
    }
}
