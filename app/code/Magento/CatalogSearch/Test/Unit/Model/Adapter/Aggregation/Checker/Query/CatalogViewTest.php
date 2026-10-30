<?php
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CatalogSearch\Test\Unit\Model\Adapter\Aggregation\Checker\Query;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Catalog\Model\Category;
use Magento\CatalogSearch\Model\Adapter\Aggregation\Checker\Query\CatalogView;
use Magento\Framework\Search\Request\Filter\Term;
use Magento\Framework\Search\Request\Query\BoolExpression;
use Magento\Framework\Search\Request\Query\Filter;
use Magento\Framework\Search\Request\QueryInterface;
use Magento\Framework\Search\RequestInterface;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CatalogViewTest extends TestCase
{
    use MockCreationTrait;
    /**
     * @var CatalogView
     */
    private $catalogViewMock;

    /**
     * @var CategoryRepositoryInterface|MockObject
     */
    private $categoryRepositoryMock;

    /**
     * @var StoreManagerInterface|MockObject
     */
    private $storeManagerMock;

    /**
     * @var RequestInterface|MockObject
     */
    private $requestMock;

    /**
     * @var QueryInterface|MockObject
     */
    private $queryMock;

    /**
     * @var Filter|MockObject
     */
    private $queryFilterMock;

    /**
     * @var Term|MockObject
     */
    private $termFilterMock;

    /**
     * @var string
     */
    private $name;

    /**
     * @var CategoryInterface|MockObject
     */
    private $categoryMock;

    /**
     * @var StoreInterface|MockObject
     */
    private $storeMock;

    protected function setUp(): void
    {
        $this->categoryRepositoryMock = $this->createMock(CategoryRepositoryInterface::class);
        $this->storeManagerMock = $this->createMock(StoreManagerInterface::class);
        $this->requestMock = $this->createMock(RequestInterface::class);
        $this->queryFilterMock = $this->createPartialMock(
            Filter::class,
            ['getReference']
        );
        $this->termFilterMock = $this->createPartialMock(
            Term::class,
            ['getValue']
        );
        $this->storeMock = $this->createMock(StoreInterface::class);
        $this->categoryMock = $this->createPartialMockWithReflection(
            Category::class,
            ['getIsAnchor']
        );
        $this->queryMock = $this->createPartialMock(
            BoolExpression::class,
            ['getMust', 'getShould', 'getType']
        );
        $this->name = 'Request';

        $this->catalogViewMock = new CatalogView($this->categoryRepositoryMock, $this->storeManagerMock, $this->name);
    }

    public function testIsApplicable()
    {
        $this->assertTrue($this->catalogViewMock->isApplicable($this->requestMock));
    }

    public function testIsNotApplicable()
    {
        $this->requestMock->expects($this->once())
            ->method('getName')
            ->willReturn($this->name);
        $this->requestMock->method('getQuery')
            ->willReturn($this->queryMock);
        $this->queryMock->expects($this->once())
            ->method('getType')
            ->willReturn(QueryInterface::TYPE_BOOL);
        $this->queryMock->method('getMust')
            ->willReturn(['category' => $this->queryFilterMock]);
        $this->queryFilterMock->method('getReference')
            ->willReturn($this->termFilterMock);
        $this->termFilterMock->method('getValue')
            ->willReturn(1);
        $this->storeManagerMock->method('getStore')
            ->willReturn($this->storeMock);
        $this->storeMock->method('getId')
            ->willReturn(1);
        $this->categoryMock->method('getIsAnchor')->willReturn(false);
        $this->categoryRepositoryMock->expects($this->once())
            ->method('get')
            ->willReturn($this->categoryMock);
        $this->assertFalse($this->catalogViewMock->isApplicable($this->requestMock));
    }
}
