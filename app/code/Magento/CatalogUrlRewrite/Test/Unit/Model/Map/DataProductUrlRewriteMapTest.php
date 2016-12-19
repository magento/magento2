<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogUrlRewrite\Test\Unit\Model\Map;

use Magento\Framework\DB\Select;
use Magento\CatalogUrlRewrite\Model\Map\DataMapPoolInterface;
use Magento\CatalogUrlRewrite\Model\Map\DataProductMap;
use Magento\CatalogUrlRewrite\Model\Map\DataCategoryUrlRewriteMap;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\TemporaryTableService;
use Magento\UrlRewrite\Service\V1\Data\UrlRewriteFactory;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;

/**
 * Class DataProductUrlRewriteMapTest
 */
class DataProductUrlRewriteMapTest extends \PHPUnit_Framework_TestCase
{
    /** @var DataMapPoolInterface|\PHPUnit_Framework_MockObject_MockObject */
    private $dataMapPoolMock;

    /** @var DataProductMap|\PHPUnit_Framework_MockObject_MockObject */
    private $dataProductMapMock;

    /** @var TemporaryTableService|\PHPUnit_Framework_MockObject_MockObject */
    private $temporaryTableServiceMock;

    /** @var UrlRewriteFactory|\PHPUnit_Framework_MockObject_MockObject */
    private $urlRewriteFactoryMock;

    /** @var UrlRewrite|\PHPUnit_Framework_MockObject_MockObject */
    private $urlRewritePlaceholderMock;

    /** @var ResourceConnection|\PHPUnit_Framework_MockObject_MockObject */
    private $connectionMock;

    /** @var DataProductMap|\PHPUnit_Framework_MockObject_MockObject */
    private $model;

    protected function setUp()
    {
        $this->dataMapPoolMock = $this->getMock(DataMapPoolInterface::class);
        $this->dataProductMapMock = $this->getMock(DataProductMap::class, [], [], '', false);
        $this->temporaryTableServiceMock = $this->getMock(TemporaryTableService::class, [], [], '', false);
        $this->urlRewriteFactoryMock = $this->getMock(UrlRewriteFactory::class, ['create'], [], '', false);
        $this->urlRewritePlaceholderMock = $this->getMock(UrlRewrite::class, [], [], '', false);
        $this->connectionMock = $this->getMock(ResourceConnection::class, [], [], '', false);

        $this->dataMapPoolMock->expects($this->any())
            ->method('getDataMap')
            ->willReturn($this->dataProductMapMock);

        $this->urlRewriteFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($this->urlRewritePlaceholderMock);

        $this->model = (new ObjectManager($this))->getObject(
            DataCategoryUrlRewriteMap::class,
            [
                'connection' => $this->connectionMock,
                'dataMapPool' => $this->dataMapPoolMock,
                'temporaryTableService' => $this->temporaryTableServiceMock,
                'urlRewriteFactory' => $this->urlRewriteFactoryMock,
                'mapData' => [],
            ]
        );
    }

    /**
     * Tests getAllData, getData and resetData functionality
     */
    public function testGetAllData()
    {
        $productStoreIds = [
            '1' => ['store_id' => 1, 'product_id' => 1],
            '2' => ['store_id' => 2, 'product_id' => 1],
            '3' => ['store_id' => 3, 'product_id' => 1],
            '4' => ['store_id' => 1, 'product_id' => 2],
            '5' => ['store_id' => 2, 'product_id' => 2],
        ];

        $productStoreIdsExpectedMap = [
            '1' => [1, 2, 3],
            '2' => [1, 2],
        ];

        $connectionMock = $this->getMock(AdapterInterface::class);
        $selectMock = $this->getMock(Select::class, [], [], '', false);

        $this->connectionMock->expects($this->any())
            ->method('getConnection')
            ->willReturn($connectionMock);
        $connectionMock->expects($this->any())
            ->method('select')
            ->willReturn($selectMock);
        $connectionMock->expects($this->any())
            ->method('fetchAll')
            ->willReturnOnConsecutiveCalls($productStoreIds, $productStoreIdsExpectedMap);
        $selectMock->expects($this->any())
            ->method('from')
            ->willReturnSelf();
        $selectMock->expects($this->any())
            ->method('joinInner')
            ->willReturnSelf();
        $selectMock->expects($this->any())
            ->method('where')
            ->willReturnSelf();

        $this->dataProductMapMock->expects($this->any())
            ->method('getAllData')
            ->willReturn([]);

        $this->temporaryTableServiceMock->expects($this->any())
            ->method('createFromSelect')
            ->withConsecutive(
                $selectMock,
                $connectionMock,
                [
                    'PRIMARY' => ['url_rewrite_id'],
                    'HASHKEY_ENTITY_STORE' => ['hash_key'],
                    'ENTITY_STORE' => ['entity_id', 'store_id']
                ]
            )
            ->willReturn('tempTableName');

        $this->assertEquals($productStoreIds, $this->model->getAllData(1));
        $this->assertEquals($productStoreIdsExpectedMap, $this->model->getData(1, '3_1'));
    }
}
