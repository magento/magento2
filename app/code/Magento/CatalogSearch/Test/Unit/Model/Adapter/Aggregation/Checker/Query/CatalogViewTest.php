<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogSearch\Test\Unit\Model\Adapter\Aggregation\Checker\Query;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\CatalogSearch\Model\Adapter\Aggregation\Checker\Query\CatalogView;
use Magento\Framework\Search\Request\Filter\Term;
use Magento\Framework\Search\Request\Query\Filter;
use Magento\Framework\Search\Request\QueryInterface;
use Magento\Framework\Search\RequestInterface;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CatalogViewTest extends TestCase
{
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
        $this->categoryRepositoryMock = $this->getMockBuilder(CategoryRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->storeManagerMock = $this->getMockBuilder(StoreManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->requestMock = $this->getMockBuilder(RequestInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->queryFilterMock = $this->getMockBuilder(Filter::class)
            ->onlyMethods(['getReference'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->termFilterMock = $this->getMockBuilder(Term::class)
            ->onlyMethods(['getValue'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->storeMock = $this->getMockBuilder(StoreInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->categoryMock = $this->getMockBuilder(CategoryInterface::class)
            ->addMethods(['getIsAnchor'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->queryMock = $this->getMockBuilder(QueryInterface::class)
            ->addMethods(['getMust', 'getShould'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
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
        $this->requestMock->expects($this->any())
            ->method('getQuery')
            ->willReturn($this->queryMock);
        $this->queryMock->expects($this->once())
            ->method('getType')
            ->willReturn(QueryInterface::TYPE_BOOL);
        $this->queryMock->expects($this->any())
            ->method('getMust')
            ->willReturn(['category' => $this->queryFilterMock]);
        $this->queryFilterMock->expects($this->any())
            ->method('getReference')
            ->willReturn($this->termFilterMock);
        $this->termFilterMock->expects($this->any())
            ->method('getValue')
            ->willReturn(1);
        $this->storeManagerMock->expects($this->any())
            ->method('getStore')
            ->willReturn($this->storeMock);
        $this->storeMock->expects($this->any())
            ->method('getId')
            ->willReturn(1);
        $this->categoryRepositoryMock->expects($this->once())
            ->method('get')
            ->willReturn($this->categoryMock);
        $this->categoryMock->expects($this->once())
            ->method('getIsAnchor')
            ->willReturn(false);
        $this->assertFalse($this->catalogViewMock->isApplicable($this->requestMock));
    }
}
