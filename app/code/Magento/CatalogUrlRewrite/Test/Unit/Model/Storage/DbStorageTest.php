<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogUrlRewrite\Test\Unit\Model\Storage;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\CatalogUrlRewrite\Model\Storage\DbStorage;
use PHPUnit\Framework\TestCase;
use Magento\UrlRewrite\Service\V1\Data\UrlRewriteFactory;
use PHPUnit_Framework_MockObject_MockObject as Mock;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\App\ResourceConnection;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;

class DbStorageTest extends TestCase
{
    /**
     * @var DbStorage
     */
    private $storage;

    /**
     * @var UrlRewriteFactory|Mock
     */
    private $urlRewriteFactory;

    /**
     * @var DataObjectHelper|Mock
     */
    private $dataObjectHelper;

    /**
     * @var AdapterInterface|Mock
     */
    private $connectionMock;

    /**
     * @var Select|Mock
     */
    private $select;

    /**
     * @var ResourceConnection|Mock
     */
    private $resource;

    /**
     * @inheritDoc
     *
     * Preparing mocks and target object.
     */
    protected function setUp()
    {
        parent::setUp();

        $this->urlRewriteFactory = $this
            ->getMockBuilder(UrlRewriteFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->dataObjectHelper = $this->createMock(DataObjectHelper::class);
        $this->connectionMock = $this->createMock(AdapterInterface::class);
        $this->select = $this->createPartialMock(
            Select::class,
            ['from', 'where', 'deleteFromSelect', 'joinLeft']
        );
        $this->resource = $this->createMock(ResourceConnection::class);

        $this->resource->expects($this->any())
            ->method('getConnection')
            ->will($this->returnValue($this->connectionMock));
        $this->connectionMock->expects($this->any())
            ->method('select')
            ->will($this->returnValue($this->select));

        $this->storage = (new ObjectManager($this))->getObject(
            DbStorage::class,
            [
                'urlRewriteFactory' => $this->urlRewriteFactory,
                'dataObjectHelper' => $this->dataObjectHelper,
                'resource' => $this->resource,
            ]
        );
    }

    public function testPrepareSelect()
    {
        //Passing expected parameters, checking select built.
        $entityType = 'custom';
        $entityId= 42;
        $storeId = 0;
        $categoryId = 2;
        $redirectType = 301;
        //Expecting this methods to be called on select
        $this->select
            ->expects($this->at(2))
            ->method('where')
            ->with('url_rewrite.entity_id IN (?)', $entityId)
            ->willReturn($this->select);
        $this->select
            ->expects($this->at(3))
            ->method('where')
            ->with('url_rewrite.entity_type IN (?)', $entityType)
            ->willReturn($this->select);
        $this->select
            ->expects($this->at(4))
            ->method('where')
            ->with('url_rewrite.store_id IN (?)', $storeId)
            ->willReturn($this->select);
        $this->select
            ->expects($this->at(5))
            ->method('where')
            ->with('url_rewrite.redirect_type IN (?)', $redirectType)
            ->willReturn($this->select);
        $this->select
            ->expects($this->at(6))
            ->method('where')
            ->with('relation.category_id = ?', $categoryId)
            ->willReturn($this->select);
        //Preparing mocks to be used
        $this->select
            ->expects($this->any())
            ->method('from')
            ->willReturn($this->select);
        $this->select
            ->expects($this->any())
            ->method('joinLeft')
            ->willReturn($this->select);
        //Indirectly calling prepareSelect
        $this->storage->findOneByData([
            UrlRewrite::ENTITY_ID => $entityId,
            UrlRewrite::ENTITY_TYPE => $entityType,
            UrlRewrite::STORE_ID => $storeId,
            UrlRewrite::REDIRECT_TYPE => $redirectType,
            UrlRewrite::METADATA => ['category_id' => $categoryId]
        ]);
    }
}
