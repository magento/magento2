<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Test\Unit\Helper;

use Magento\Customer\Api\CustomerMetadataInterface;
use Magento\Customer\Api\Data\AttributeMetadataInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Helper\View;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Escaper;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ViewTest extends TestCase
{
    /** @var Context|MockObject */
    protected $context;

    /** @var View|MockObject */
    protected $object;

    /** @var CustomerMetadataInterface|MockObject */
    protected $customerMetadataService;

    /**
     * @var Escaper|MockObject
     */
    private $escaperMock;

    protected function setUp(): void
    {
        $this->context = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->customerMetadataService = $this->getMockForAbstractClass(CustomerMetadataInterface::class);

        $attributeMetadata = $this->getMockForAbstractClass(AttributeMetadataInterface::class);
        $attributeMetadata->expects($this->any())->method('isVisible')->willReturn(true);
        $this->customerMetadataService->expects($this->any())
            ->method('getAttributeMetadata')
            ->willReturn($attributeMetadata);
        $this->escaperMock = $this->createMock(Escaper::class);

        $this->object = new View($this->context, $this->customerMetadataService, $this->escaperMock);
    }

    /**
     * @dataProvider getCustomerServiceDataProvider
     */
    public function testGetCustomerName($prefix, $firstName, $middleName, $lastName, $suffix, $result)
    {
        $customerData = $this->getMockBuilder(CustomerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $customerData->expects($this->any())
            ->method('getPrefix')->willReturn($prefix);
        $customerData->expects($this->any())
            ->method('getFirstname')->willReturn($firstName);
        $customerData->expects($this->any())
            ->method('getMiddlename')->willReturn($middleName);
        $customerData->expects($this->any())
            ->method('getLastname')->willReturn($lastName);
        $customerData->expects($this->any())
            ->method('getSuffix')->willReturn($suffix);
        $this->escaperMock->expects($this->once())->method('escapeHtml')->with($result)->willReturn($result);
        $this->assertEquals($result, $this->object->getCustomerName($customerData));
    }

    /**
     * @return array
     */
    public function getCustomerServiceDataProvider()
    {
        return [
            [
                'prefix', //prefix
                'first_name', //first_name
                'middle_name', //middle_name
                'last_name', //last_name
                'suffix', //suffix
                'prefix first_name middle_name last_name suffix', //result name
            ],
            [
                '', //prefix
                'first_name', //first_name
                'middle_name', //middle_name
                'last_name', //last_name
                'suffix', //suffix
                'first_name middle_name last_name suffix', //result name
            ],
            [
                'prefix', //prefix
                'first_name', //first_name
                '', //middle_name
                'last_name', //last_name
                'suffix', //suffix
                'prefix first_name last_name suffix', //result name
            ],
            [
                'prefix', //prefix
                'first_name', //first_name
                'middle_name', //middle_name
                'last_name', //last_name
                '', //suffix
                'prefix first_name middle_name last_name', //result name
            ],
            [
                '', //prefix
                'first_name', //first_name
                '', //middle_name
                'last_name', //last_name
                'suffix', //suffix
                'first_name last_name suffix', //result name
            ],
            [
                'prefix', //prefix
                'first_name', //first_name
                '', //middle_name
                'last_name', //last_name
                '', //suffix
                'prefix first_name last_name', //result name
            ],
            [
                '', //prefix
                'first_name', //first_name
                'middle_name', //middle_name
                'last_name', //last_name
                '', //suffix
                'first_name middle_name last_name', //result name
            ],
        ];
    }
}
