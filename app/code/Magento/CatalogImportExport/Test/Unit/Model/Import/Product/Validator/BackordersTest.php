<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CatalogImportExport\Test\Unit\Model\Import\Product\Validator;

use Magento\CatalogImportExport\Model\Import\Product;
use Magento\CatalogImportExport\Model\Import\Product\Validator\Backorders;
use Magento\CatalogInventory\Model\Source\Backorders as BackordersSource;
use Magento\ImportExport\Model\Import;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class BackordersTest extends TestCase
{
    /**
     * @var BackordersSource|MockObject
     */
    private $backordersSourceMock;

    /**
     * @var Backorders
     */
    private $backorders;

    protected function setUp(): void
    {
        $this->backordersSourceMock = $this->createMock(BackordersSource::class);
        $this->backorders = new Backorders($this->backordersSourceMock);

        $this->backordersSourceMock->method('toOptionArray')
            ->willReturn([
                ['value' => 0, 'label' => 'No Backorders'],
                ['value' => 1, 'label' => 'Allow Qty Below 0'],
                ['value' => 2, 'label' => 'Allow Qty Below 0 and Notify Customer'],
            ]);

        $contextStub = $this->createMock(Product::class);
        $contextStub->method('getEmptyAttributeValueConstant')
            ->willReturn(Import::DEFAULT_EMPTY_ATTRIBUTE_VALUE_CONSTANT);
        $contextStub->method('retrieveMessageTemplate')
            ->willReturnMap([
                [Backorders::ERROR_INVALID_ATTRIBUTE_TYPE, 'Value for \'%s\' attribute contains incorrect value'],
                [Backorders::ERROR_INVALID_ATTRIBUTE_OPTION, 'Value for \'%s\' attribute contains unacceptable value'],
            ]);
        $this->backorders->init($contextStub);
    }

    #[
        TestWith([['allow_backorders' => 0], true]),
        TestWith([['allow_backorders' => 1], true]),
        TestWith([['allow_backorders' => 2], true]),
        TestWith([['allow_backorders' => Import::DEFAULT_EMPTY_ATTRIBUTE_VALUE_CONSTANT], true]),
        TestWith([['some_field' => 'value'], true]),
        TestWith([['allow_backorders' => 3], false]),
        TestWith([['allow_backorders' => 'value'], false]),
    ]
    public function testIsValid(array $value, bool $expected): void
    {
        $result = $this->backorders->isValid($value);
        $this->assertEquals($expected, $result);
    }
}
