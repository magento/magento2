<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogUrlRewriteGraphQl\Test\Unit\Model\Resolver;

use Magento\CatalogUrlRewriteGraphQl\Model\Resolver\ProductUrlSuffix;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\GraphQl\Model\Query\ContextExtensionInterface;
use Magento\GraphQl\Model\Query\ContextInterface;
use Magento\Store\Api\Data\StoreInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * Test for \Magento\CatalogUrlRewriteGraphQl\Model\Resolver\ProductUrlSuffix.
 */
class ProductUrlSuffixTest extends TestCase
{
    /**
     * @var ScopeConfigInterface|MockObject
     */
    private $scopeConfigMock;

    /**
     * @var ContextInterface|MockObject
     */
    private $contextMock;

    /**
     * @var ContextExtensionInterface|MockObject
     */
    private $contextExtensionMock;

    /**
     * @var StoreInterface|MockObject
     */
    private $storeMock;

    /**
     * @var Field|MockObject
     */
    private $fieldMock;

    /**
     * @var ResolveInfo|MockObject
     */
    private $resolveInfoMock;

    /**
     * @var ProductUrlSuffix
     */
    private $resolver;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->contextMock = $this->getMockBuilder(ContextInterface::class)
            ->disableOriginalConstructor()
            ->onlyMethods(
                [
                    'getExtensionAttributes'
                ]
            )
            ->getMockForAbstractClass();

        $this->contextExtensionMock = $this->getMockBuilder(ContextExtensionInterface::class)
            ->addMethods(
                [
                    'getStore'
                ]
            )
            ->getMockForAbstractClass();

        $this->storeMock = $this->getMockBuilder(StoreInterface::class)
            ->onlyMethods(
                [
                    'getId'
                ]
            )
            ->getMockForAbstractClass();

        $this->fieldMock = $this->getMockBuilder(Field::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->resolveInfoMock = $this->getMockBuilder(ResolveInfo::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->scopeConfigMock = $this->getMockBuilder(ScopeConfigInterface::class)
            ->getMockForAbstractClass();

        $this->resolver = new ProductUrlSuffix(
            $this->scopeConfigMock
        );
    }

    /**
     * Verify that empty string is returned when config value is null
     */
    public function testNullValue()
    {
        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->willReturn(null);

        $this->contextMock
            ->expects($this->once())
            ->method('getExtensionAttributes')
            ->willReturn($this->contextExtensionMock);

        $this->contextExtensionMock
            ->expects($this->once())
            ->method('getStore')
            ->willReturn($this->storeMock);

        $this->storeMock
            ->expects($this->once())
            ->method('getId')
            ->willReturn(1);

        $this->assertEquals(
            '',
            $this->resolver->resolve(
                $this->fieldMock,
                $this->contextMock,
                $this->resolveInfoMock
            )
        );
    }

    /**
     * Verify that the configured value is returned
     */
    public function testNonNullValue()
    {
        $value = 'html';
        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->willReturn($value);

        $this->contextMock
            ->expects($this->once())
            ->method('getExtensionAttributes')
            ->willReturn($this->contextExtensionMock);

        $this->contextExtensionMock
            ->expects($this->once())
            ->method('getStore')
            ->willReturn($this->storeMock);

        $this->storeMock
            ->expects($this->once())
            ->method('getId')
            ->willReturn(1);

        $this->assertEquals(
            $value,
            $this->resolver->resolve(
                $this->fieldMock,
                $this->contextMock,
                $this->resolveInfoMock
            )
        );
    }
}
