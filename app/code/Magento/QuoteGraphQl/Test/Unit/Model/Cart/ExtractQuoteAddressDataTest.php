<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\QuoteGraphQl\Test\Unit\Model\Cart;

use Magento\Framework\Api\AttributeInterface;
use Magento\Framework\Api\ExtensibleDataObjectConverter;
use Magento\Framework\GraphQl\Query\Uid;
use Magento\Quote\Model\Quote\Address as QuoteAddress;
use Magento\QuoteGraphQl\Model\Cart\ExtractQuoteAddressData;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Magento\EavGraphQl\Model\Output\Value\GetAttributeValueInterface;

/**
 * Test for ExtractQuoteAddressData
 *
 * @see ExtractQuoteAddressData
 */
class ExtractQuoteAddressDataTest extends TestCase
{
    /**
     * @var ExtractQuoteAddressData
     */
    private ExtractQuoteAddressData $extractQuoteAddressData;

    /**
     * @var ExtensibleDataObjectConverter|MockObject
     */
    private ExtensibleDataObjectConverter|MockObject $dataObjectConverterMock;

    /**
     * @var Uid|MockObject
     */
    private Uid|MockObject $uidEncoderMock;

    /**
     * @var GetAttributeValueInterface|MockObject
     */
    private GetAttributeValueInterface|MockObject $getAttributeValueMock;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->dataObjectConverterMock = $this->createMock(ExtensibleDataObjectConverter::class);
        $this->uidEncoderMock = $this->createMock(Uid::class);
        $this->getAttributeValueMock = $this->createMock(GetAttributeValueInterface::class);

        $this->extractQuoteAddressData = new ExtractQuoteAddressData(
            $this->dataObjectConverterMock,
            $this->uidEncoderMock,
            $this->getAttributeValueMock
        );
    }

    /**
     * Test that null, integer and array custom attribute values do not cause a TypeError
     * and are normalized to strings before being passed to the value provider
     *
     * @return void
     */
    public function testExecuteNormalizesNonStringCustomAttributeValues(): void
    {
        $nullAttributeMock = $this->createMock(AttributeInterface::class);
        $nullAttributeMock->method('getAttributeCode')->willReturn('null_attribute');
        $nullAttributeMock->method('getValue')->willReturn(null);

        $intAttributeMock = $this->createMock(AttributeInterface::class);
        $intAttributeMock->method('getAttributeCode')->willReturn('int_attribute');
        $intAttributeMock->method('getValue')->willReturn(123);

        $arrayAttributeMock = $this->createMock(AttributeInterface::class);
        $arrayAttributeMock->method('getAttributeCode')->willReturn('multiselect_attribute');
        $arrayAttributeMock->method('getValue')->willReturn(['a', 'b']);

        $addressMock = $this->createMock(QuoteAddress::class);
        $addressMock
            ->method('getCustomAttributes')
            ->willReturn([$nullAttributeMock, $intAttributeMock, $arrayAttributeMock]);

        $this->dataObjectConverterMock->method('toFlatArray')->willReturn([]);
        $this->uidEncoderMock->method('encode')->willReturn('encoded_uid');

        $expectedValues = [
            'null_attribute' => '',
            'int_attribute' => '123',
            'multiselect_attribute' => 'a,b',
        ];

        $this->getAttributeValueMock
            ->expects($this->exactly(3))
            ->method('execute')
            ->willReturnCallback(function ($entity, $code, $value) use ($expectedValues) {
                $this->assertSame('customer_address', $entity);
                $this->assertSame($expectedValues[$code], $value);
                return [
                    'code' => $code,
                    'value' => $value,
                    'sort_order' => 0,
                ];
            });

        $result = $this->extractQuoteAddressData->execute($addressMock);

        $this->assertArrayHasKey('custom_attributes', $result);
        $this->assertCount(3, $result['custom_attributes']);
    }
}
