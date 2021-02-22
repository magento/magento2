<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogSearch\Test\Unit\Model\Adapter\Aggregation\Checker\Query;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\CatalogSearch\Model\Adapter\Aggregation\Checker\Query\CatalogView;
use Magento\Framework\Search\RequestInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Search\Request\QueryInterface;
use Magento\Framework\Search\Request\Query\Filter;
use Magento\Framework\Search\Request\Filter\Term;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Catalog\Api\Data\CategoryInterface;

class CatalogViewTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var CatalogView
     */
    private $catalogViewMock;

    /**
     * @var CategoryRepositoryInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $categoryRepositoryMock;

    /**
     * @var StoreManagerInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $storeManagerMock;

    /**
     * @var RequestInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $requestMock;

    /**
     * @var QueryInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $queryMock;

    /**
     * @var Filter|\PHPUnit\Framework\MockObject\MockObject
     */
    private $queryFilterMock;

    /**
     * @var Term|\PHPUnit\Framework\MockObject\MockObject
     */
    private $termFilterMock;

    /**
     * @var string
     */
    private $name;

    /**
     * @var CategoryInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $categoryMock;

    /**
     * @var StoreInterface|\PHPUnit\Framework\MockObject\MockObject
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
            ->setMethods(['getReference'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->termFilterMock = $this->getMockBuilder(Term::class)
            ->setMethods(['getValue'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->storeMock = $this->getMockBuilder(StoreInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->categoryMock = $this->getMockBuilder(CategoryInterface::class)
            ->setMethods(['getIsAnchor'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->queryMock = $this->getMockBuilder(QueryInterface::class)
            ->setMethods(['getMust', 'getShould'])
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
