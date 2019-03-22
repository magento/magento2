<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Eav\Test\Unit\Model\TypeLocator;

use Magento\Eav\Model\TypeLocator\SimpleType;
use Magento\Eav\Model\AttributeRepository;
use Magento\Framework\Webapi\CustomAttribute\ServiceTypeListInterface;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\Framework\Reflection\TypeProcessor;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class SimpleTypeTest
 *
 * @package Magento\Eav\Test\Unit\Model\TypeLocator
 */
class SimpleTypeTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Test testGetType method
     * @return void
     */
    public function testGetTypeWithNonexistentAttribute()
    {
        $getException = new NoSuchEntityException();
        $expected = TypeProcessor::NORMALIZED_ANY_TYPE;

        /** @var AttributeRepository|\PHPUnit_Framework_MockObject_MockObject $attributeRepositoryMock */
        $attributeRepositoryMock = $this->createMock(AttributeRepository::class);
        $attributeRepositoryMock->expects($this->any())
                                ->method('get')
                                ->willThrowException($getException);

        /** @var ServiceTypeListInterface|\PHPUnit_Framework_MockObject_MockObject $serviceTypeListMock */
        $serviceTypeListMock = $this->createMock(ServiceTypeListInterface::class);

        /** @var SimpleType|\PHPUnit_Framework_MockObject_MockObject $simpleType */
        $simpleType = new SimpleType(
            $attributeRepositoryMock,
            $serviceTypeListMock
        );

        $this->assertSame($expected, $simpleType->getType('testAttributeCode', 'testEntityType'));
    }

    /**
     * Test testGetType method
     * @return void
     */
    public function testGetTypeWithMultiselectFrontendInput()
    {
        $getFrontendInputReturn = 'multiselect';
        $expected = TypeProcessor::NORMALIZED_ANY_TYPE;

        /** @var Attribute|\PHPUnit_Framework_MockObject_MockObject $attributeMock */
        $attributeMock = $this->createMock(Attribute::class);
        $attributeMock->expects($this->any())
                      ->method('getFrontendInput')
                      ->willReturn($getFrontendInputReturn);

        /** @var AttributeRepository|\PHPUnit_Framework_MockObject_MockObject $attributeRepositoryMock */
        $attributeRepositoryMock = $this->createMock(AttributeRepository::class);
        $attributeRepositoryMock->expects($this->any())
                                ->method('get')
                                ->willReturn($attributeMock);

        /** @var ServiceTypeListInterface|\PHPUnit_Framework_MockObject_MockObject $serviceTypeListMock */
        $serviceTypeListMock = $this->createMock(ServiceTypeListInterface::class);

        /** @var SimpleType|\PHPUnit_Framework_MockObject_MockObject $simpleType */
        $simpleType = new SimpleType(
            $attributeRepositoryMock,
            $serviceTypeListMock
        );

        $this->assertSame($expected, $simpleType->getType('testAttributeCode', 'testEntityType'));
    }

    /**
     * Test testGetType method
     * @return void
     */
    public function testGetTypeWithBackendTypeInMap()
    {
        $getFrontendInputReturn = 'textarea';
        $getBackendTypeReturn = 'text';
        $expected = TypeProcessor::NORMALIZED_STRING_TYPE;

        /** @var Attribute|\PHPUnit_Framework_MockObject_MockObject $attributeMock */
        $attributeMock = $this->createMock(Attribute::class);
        $attributeMock->expects($this->any())
                      ->method('getFrontendInput')
                      ->willReturn($getFrontendInputReturn);
        $attributeMock->expects($this->any())
                      ->method('getBackendType')
                      ->willReturn($getBackendTypeReturn);

        /** @var AttributeRepository|\PHPUnit_Framework_MockObject_MockObject $attributeRepositoryMock */
        $attributeRepositoryMock = $this->createMock(AttributeRepository::class);
        $attributeRepositoryMock->expects($this->any())
                                ->method('get')
                                ->willReturn($attributeMock);

        /** @var ServiceTypeListInterface|\PHPUnit_Framework_MockObject_MockObject $serviceTypeListMock */
        $serviceTypeListMock = $this->createMock(ServiceTypeListInterface::class);

        /** @var SimpleType|\PHPUnit_Framework_MockObject_MockObject $simpleType */
        $simpleType = new SimpleType(
            $attributeRepositoryMock,
            $serviceTypeListMock
        );

        $this->assertSame($expected, $simpleType->getType('testAttributeCode', 'testEntityType'));
    }

    /**
     * Test testGetType method
     * @return void
     */
    public function testGetTypeWithBackendTypeNotInMap()
    {
        $getFrontendInputReturn = 'textarea';
        $getBackendTypeReturn = 'testBackendTypeNotInMap';
        $expected = TypeProcessor::NORMALIZED_ANY_TYPE;

        /** @var Attribute|\PHPUnit_Framework_MockObject_MockObject $attributeMock */
        $attributeMock = $this->createMock(Attribute::class);
        $attributeMock->expects($this->any())
                      ->method('getFrontendInput')
                      ->willReturn($getFrontendInputReturn);
        $attributeMock->expects($this->any())
                      ->method('getBackendType')
                      ->willReturn($getBackendTypeReturn);

        /** @var AttributeRepository|\PHPUnit_Framework_MockObject_MockObject $attributeRepositoryMock */
        $attributeRepositoryMock = $this->createMock(AttributeRepository::class);
        $attributeRepositoryMock->expects($this->any())
                                ->method('get')
                                ->willReturn($attributeMock);

        /** @var ServiceTypeListInterface|\PHPUnit_Framework_MockObject_MockObject $serviceTypeListMock */
        $serviceTypeListMock = $this->createMock(ServiceTypeListInterface::class);

        /** @var SimpleType|\PHPUnit_Framework_MockObject_MockObject $simpleType */
        $simpleType = new SimpleType(
            $attributeRepositoryMock,
            $serviceTypeListMock
        );

        $this->assertSame($expected, $simpleType->getType('testAttributeCode', 'testEntityType'));
    }
}
