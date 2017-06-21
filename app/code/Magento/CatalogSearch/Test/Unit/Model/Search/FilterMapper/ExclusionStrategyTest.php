<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogSearch\Test\Unit\Model\Search\FilterMapper;

use Magento\CatalogSearch\Model\Search\FilterMapper\ExclusionStrategy;
use Magento\Framework\App\ResourceConnection;
use Magento\Indexer\Model\ResourceModel\FrontendResource;
use Magento\Store\Model\StoreManagerInterface;
use Magento\CatalogSearch\Model\Adapter\Mysql\Filter\AliasResolver;
use Magento\Framework\DB\Select;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Search\Request\Filter\Term;
use Magento\Store\Api\Data\WebsiteInterface;

class ExclusionStrategyTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ExclusionStrategy
     */
    private $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $resourceConnectionMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $frontendResourceMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $adapterMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $storeManagerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $aliasResolverMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $categoryProductFrontendMock;

    protected function setUp()
    {
        $this->resourceConnectionMock = $this->getMock(ResourceConnection::class, [], [], '', false);

        $this->frontendResourceMock = $this->getMock(FrontendResource::class, [], [], '', false);
        $this->adapterMock = $this->getMock(AdapterInterface::class);
        $this->resourceConnectionMock->expects($this->any())->method('getConnection')->willReturn($this->adapterMock);
        $this->storeManagerMock = $this->getMock(StoreManagerInterface::class);
        $this->aliasResolverMock = $this->getMock(AliasResolver::class, [], [], '', false);
        $this->categoryProductFrontendMock = $this->getMock(FrontendResource::class, [], [], '', false);

        $this->model = new ExclusionStrategy(
            $this->resourceConnectionMock,
            $this->storeManagerMock,
            $this->aliasResolverMock,
            $this->frontendResourceMock,
            $this->categoryProductFrontendMock
        );
    }

    public function testApplyUsesFrontendPriceIndexerTableIfAttributeCodeIsPrice()
    {
        $attributeCode = 'price';
        $websiteId = 1;
        $selectMock = $this->getMock(Select::class, [], [], '', false);
        $selectMock->expects($this->any())->method('joinInner')->willReturnSelf();
        $selectMock->expects($this->any())->method('getPart')->willReturn([]);

        $searchFilterMock = $this->getMock(Term::class, [], [], '', false);
        $searchFilterMock->expects($this->any())->method('getField')->willReturn($attributeCode);

        $websiteMock = $this->getMock(WebsiteInterface::class);
        $websiteMock->expects($this->any())->method('getId')->willReturn($websiteId);
        $this->storeManagerMock->expects($this->any())->method('getWebsite')->willReturn($websiteMock);

        // verify that frontend indexer table is used
        $this->frontendResourceMock->expects($this->once())->method('getMainTable');

        $this->assertTrue($this->model->apply($searchFilterMock, $selectMock));
    }
}
