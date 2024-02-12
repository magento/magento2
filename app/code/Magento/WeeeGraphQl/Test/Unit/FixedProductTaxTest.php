<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\WeeeGraphQl\Test\Unit;

use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\GraphQl\Model\Query\ContextExtensionInterface;
use Magento\Weee\Helper\Data as WeeeHelper;
use Magento\WeeeGraphQl\Model\Resolver\FixedProductTax;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class FixedProductTaxTest extends TestCase
{
    const STUB_STORE_ID = 1;

    /**
     * @var MockObject|ContextInterface
     */
    private $contextMock;

    /**
     * @var MockObject|ContextExtensionInterface
     */
    private $extensionAttributesMock;

    /**
     * @var FixedProductTax
     */
    private $resolver;

    /**
     * @var MockObject|WeeeHelper
     */
    private $weeeHelperMock;

    /**
     * @var MockObject|DataObject
     */
    private $productMock;

    /**
     * Build the Testing Environment
     */
    protected function setUp(): void
    {
        $this->contextMock = $this->getMockBuilder(ContextInterface::class)
            ->addMethods(['getExtensionAttributes'])
            ->getMockForAbstractClass();

        $this->extensionAttributesMock = $this->getMockBuilder(ContextExtensionInterface::class)
            ->addMethods(['getStore', 'setStore', 'getIsCustomer', 'setIsCustomer'])
            ->getMockForAbstractClass();

        $this->contextMock->method('getExtensionAttributes')
            ->willReturn($this->extensionAttributesMock);

        $this->productMock = $this->getMockBuilder(DataObject::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->weeeHelperMock = $this->getMockBuilder(WeeeHelper::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['isEnabled', 'getProductWeeeAttributesForDisplay'])
            ->getMock();

        $objectManager = new ObjectManager($this);
        $this->resolver = $objectManager->getObject(FixedProductTax::class, [
            'weeeHelper' => $this->weeeHelperMock
        ]);
    }

    /**
     * Verifies if the Exception is being thrown when no Product Model passed to resolver
     */
    public function testExceptionWhenNoModelSpecified(): void
    {
        $this->expectException(LocalizedException::class);
        $this->expectExceptionMessageMatches('/value should be specified/');

        $this->resolver->resolve(
            $this->getFieldStub(),
            null,
            $this->getResolveInfoStub()
        );
    }

    /**
     * Verifies that Attributes for display are not being fetched if feature not enabled in store
     */
    public function testNotGettingAttributesWhenWeeeDisabledForStore(): void
    {
        // Given
        $this->extensionAttributesMock->method('getStore')
            ->willreturn(self::STUB_STORE_ID);

        // When
        $this->weeeHelperMock->method('isEnabled')
            ->with(self::STUB_STORE_ID)
            ->willReturn(false);

        // Then
        $this->weeeHelperMock->expects($this->never())
            ->method('getProductWeeeAttributesForDisplay');

        $this->resolver->resolve(
            $this->getFieldStub(),
            $this->contextMock,
            $this->getResolveInfoStub(),
            ['model' => $this->productMock]
        );
    }

    /**
     * Returns stub for Field
     *
     * @return MockObject|Field
     */
    private function getFieldStub(): Field
    {
        /** @var MockObject|Field $fieldMock */
        $fieldMock = $this->getMockBuilder(Field::class)
            ->disableOriginalConstructor()
            ->getMock();
        return $fieldMock;
    }

    /**
     * Returns stub for ResolveInfo
     *
     * @return MockObject|ResolveInfo
     */
    private function getResolveInfoStub(): ResolveInfo
    {
        /** @var MockObject|ResolveInfo $resolveInfoMock */
        $resolveInfoMock = $this->getMockBuilder(ResolveInfo::class)
            ->disableOriginalConstructor()
            ->getMock();
        return $resolveInfoMock;
    }
}
