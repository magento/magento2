<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CmsGraphQl\Test\Unit\Model\Resolver;

use Magento\CmsGraphQl\Model\Resolver\DataProvider\Page as PageDataProvider;
use Magento\CmsGraphQl\Model\Resolver\Page;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use Magento\GraphQl\Model\Query\Context;
use Magento\GraphQl\Model\Query\ContextExtensionInterface;
use Magento\Store\Api\Data\StoreInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class PageTest extends TestCase
{
    use MockCreationTrait;

    /**
     * @var PageDataProvider|MockObject
     */
    private PageDataProvider $pageDataProviderMock;

    /**
     * @var Field|MockObject
     */
    private Field $fieldMock;

    /**
     * @var Context|MockObject
     */
    private Context $contextMock;

    /**
     * @var ResolveInfo|MockObject
     */
    private ResolveInfo $resolveInfoMock;

    /**
     * @var Page
     */
    private Page $resolver;

    protected function setUp(): void
    {
        $this->pageDataProviderMock = $this->createMock(PageDataProvider::class);
        $this->fieldMock = $this->createStub(Field::class);
        $this->contextMock = $this->createStub(Context::class);
        $this->resolveInfoMock = $this->createStub(ResolveInfo::class);

        $this->resolver = new Page($this->pageDataProviderMock);
    }

    /**
     * Test that an exception is thrown when neither id nor identifier is provided
     *
     * @return void
     */
    public function testResolveThrowsExceptionWhenIdAndIdentifierAreMissing(): void
    {
        $this->pageDataProviderMock->expects($this->never())
            ->method('getDataByPageId');

        $this->pageDataProviderMock->expects($this->never())
            ->method('getDataByPageIdentifier');

        $this->expectException(GraphQlInputException::class);

        $this->resolver->resolve($this->fieldMock, $this->contextMock, $this->resolveInfoMock, null, []);
    }

    /**
     * Test that page data is resolved by id and getDataByPageId is called with exactly two parameters
     *
     * @return void
     */
    public function testResolveCallsGetDataByPageIdWithTwoParameters(): void
    {
        $expectedData = ['title' => 'Page Title'];

        $this->pageDataProviderMock->expects($this->once())
            ->method('getDataByPageId')
            ->with(1, $this->resolveInfoMock)
            ->willReturn($expectedData);

        $this->pageDataProviderMock->expects($this->never())
            ->method('getDataByPageIdentifier');

        $result = $this->resolver->resolve(
            $this->fieldMock,
            $this->contextMock,
            $this->resolveInfoMock,
            null,
            ['id' => 1]
        );

        $this->assertSame($expectedData, $result);
    }

    /**
     * Test that page data is resolved by identifier and getDataByPageIdentifier is called with exactly
     * three parameters
     *
     * @return void
     */
    public function testResolveCallsGetDataByPageIdentifierWithThreeParameters(): void
    {
        $expectedData = ['title' => 'Page Title'];

        $contextMock = $this->createMock(Context::class);
        $contextExtensionMock = $this->createPartialMockWithReflection(
            ContextExtensionInterface::class,
            ['getStore']
        );
        $storeMock = $this->createMock(StoreInterface::class);

        $contextMock->expects($this->once())
            ->method('getExtensionAttributes')
            ->willReturn($contextExtensionMock);

        $contextExtensionMock->expects($this->once())
            ->method('getStore')
            ->willReturn($storeMock);

        $storeMock->expects($this->once())
            ->method('getId')
            ->willReturn(1);

        $this->pageDataProviderMock->expects($this->once())
            ->method('getDataByPageIdentifier')
            ->with('page-identifier', 1, $this->resolveInfoMock)
            ->willReturn($expectedData);

        $this->pageDataProviderMock->expects($this->never())
            ->method('getDataByPageId');

        $result = $this->resolver->resolve(
            $this->fieldMock,
            $contextMock,
            $this->resolveInfoMock,
            null,
            ['identifier' => 'page-identifier']
        );

        $this->assertSame($expectedData, $result);
    }

    /**
     * Test that a NoSuchEntityException thrown by the data provider is translated into a
     * GraphQlNoSuchEntityException
     *
     * @return void
     */
    public function testResolveThrowsGraphQlExceptionWhenPageIsNotFound(): void
    {
        $this->pageDataProviderMock->expects($this->once())
            ->method('getDataByPageId')
            ->with(1, $this->resolveInfoMock)
            ->willThrowException(new NoSuchEntityException(__('Page does not exist')));

        $this->expectException(GraphQlNoSuchEntityException::class);

        $this->resolver->resolve($this->fieldMock, $this->contextMock, $this->resolveInfoMock, null, ['id' => 1]);
    }
}
