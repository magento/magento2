<?php
/**
 * Copyright 2020 Adobe
 * All Rights Reserved.
 */

namespace Magento\CatalogUrlRewriteGraphQl\Test\Unit\Model\Resolver;

use Magento\CatalogUrlRewriteGraphQl\Model\Resolver\ProductUrlSuffix;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use Magento\GraphQl\Model\Query\ContextExtensionInterface;
use Magento\GraphQl\Model\Query\ContextInterface;
use Magento\Store\Api\Data\StoreInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test for \Magento\CatalogUrlRewriteGraphQl\Model\Resolver\ProductUrlSuffix.
 */
class ProductUrlSuffixTest extends TestCase
{
    use MockCreationTrait;

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
        $this->contextMock = $this->createMock(ContextInterface::class);

        $this->contextExtensionMock = $this->createPartialMockWithReflection(
            ContextExtensionInterface::class,
            ['getStore']
        );

        $this->storeMock = $this->createMock(StoreInterface::class);

        $this->fieldMock = $this->createMock(Field::class);

        $this->resolveInfoMock = $this->createMock(ResolveInfo::class);

        $this->scopeConfigMock = $this->createMock(ScopeConfigInterface::class);

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
