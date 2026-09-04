<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CustomerGraphQl\Test\Unit\Model\Customer\Address;

use Magento\Customer\Api\AddressMetadataInterface;
use Magento\Customer\Api\Data\AddressInterface;
use Magento\Customer\Model\Customer;
use Magento\Customer\Model\CustomerFactory;
use Magento\Customer\Model\ResourceModel\Customer as CustomerResourceModel;
use Magento\CustomerGraphQl\Model\Customer\Address\ExtractCustomerAddressData;
use Magento\EavGraphQl\Model\Output\Value\GetAttributeValueInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\Webapi\ServiceOutputProcessor;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test for ExtractCustomerAddressData
 *
 * @see ExtractCustomerAddressData
 */
class ExtractCustomerAddressDataTest extends TestCase
{
    /**
     * @var ExtractCustomerAddressData
     */
    private ExtractCustomerAddressData $extractCustomerAddressData;

    /**
     * @var ServiceOutputProcessor|MockObject
     */
    private ServiceOutputProcessor|MockObject $serviceOutputProcessorMock;

    /**
     * @var SerializerInterface|MockObject
     */
    private SerializerInterface|MockObject $jsonSerializerMock;

    /**
     * @var CustomerResourceModel|MockObject
     */
    private CustomerResourceModel|MockObject $customerResourceModelMock;

    /**
     * @var CustomerFactory|MockObject
     */
    private CustomerFactory|MockObject $customerFactoryMock;

    /**
     * @var GetAttributeValueInterface|MockObject
     */
    private GetAttributeValueInterface|MockObject $getAttributeValueMock;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->serviceOutputProcessorMock = $this->createMock(ServiceOutputProcessor::class);
        $this->jsonSerializerMock = $this->createMock(SerializerInterface::class);
        $this->customerResourceModelMock = $this->createMock(CustomerResourceModel::class);
        $this->customerFactoryMock = $this->createMock(CustomerFactory::class);
        $this->getAttributeValueMock = $this->createMock(GetAttributeValueInterface::class);

        $customerMock = $this->createMock(Customer::class);
        $customerMock->method('getDefaultBillingAddress')->willReturn(false);
        $customerMock->method('getDefaultShippingAddress')->willReturn(false);
        $this->customerFactoryMock->method('create')->willReturn($customerMock);

        $this->extractCustomerAddressData = new ExtractCustomerAddressData(
            $this->serviceOutputProcessorMock,
            $this->jsonSerializerMock,
            $this->customerResourceModelMock,
            $this->customerFactoryMock,
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
        $addressMock = $this->createMock(AddressInterface::class);
        $addressMock->method('getCustomerId')->willReturn(1);
        $addressMock->method('getId')->willReturn(1);

        $this->serviceOutputProcessorMock
            ->method('process')
            ->willReturn([
                'id' => 1,
                'country_id' => 'US',
                'custom_attributes' => [
                    ['attribute_code' => 'null_attribute', 'value' => null],
                    ['attribute_code' => 'int_attribute', 'value' => 123],
                    ['attribute_code' => 'multiselect_attribute', 'value' => ['a', 'b']],
                ],
            ]);

        $expectedValues = [
            'null_attribute' => '',
            'int_attribute' => '123',
            'multiselect_attribute' => 'a,b',
        ];

        $this->getAttributeValueMock
            ->expects($this->exactly(3))
            ->method('execute')
            ->willReturnCallback(function ($entity, $code, $value) use ($expectedValues) {
                $this->assertSame(AddressMetadataInterface::ENTITY_TYPE_ADDRESS, $entity);
                $this->assertSame($expectedValues[$code], $value);
                return [
                    'code' => $code,
                    'value' => $value,
                    'sort_order' => 0,
                ];
            });

        $result = $this->extractCustomerAddressData->execute($addressMock);

        $this->assertArrayHasKey('custom_attributesV2', $result);
        $this->assertCount(3, $result['custom_attributesV2']);
    }
}
