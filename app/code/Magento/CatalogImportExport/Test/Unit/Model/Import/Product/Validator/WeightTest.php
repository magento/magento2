<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogImportExport\Test\Unit\Model\Import\Product\Validator;

use Magento\CatalogImportExport\Model\Import\Product;
use Magento\CatalogImportExport\Model\Import\Product\Validator\Weight;
use Magento\ImportExport\Model\Import;
use PHPUnit\Framework\TestCase;

class WeightTest extends TestCase
{
    /**
     * @var Weight
     */
    private $weight;

    protected function setUp(): void
    {
        $this->weight = new Weight();

        $contextStub = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->getMock();
        $contextStub->expects($this->any())
            ->method('getEmptyAttributeValueConstant')
            ->willReturn(Import::DEFAULT_EMPTY_ATTRIBUTE_VALUE_CONSTANT);

        $contextStub->method('retrieveMessageTemplate')->willReturn('some template');
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
            [true, ['weight' => Import::DEFAULT_EMPTY_ATTRIBUTE_VALUE_CONSTANT]],
            [false, ['weight' => '__EMPTY__VALUE__TEST__']],
        ];
    }
}
