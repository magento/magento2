<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sitemap\Test\Unit\Model\ResourceModel\Cms;

use Magento\Cms\Api\Data\PageInterface;
use Magento\Cms\Model\GetUtilityPageIdentifiers;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DataObject;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\EntityManager\EntityManager;
use Magento\Framework\EntityManager\EntityMetadataInterface;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Sitemap\Model\ResourceModel\Cms\Page;
use PHPUnit\Framework\TestCase;

/**
 * Provide tests for Cms Page resource model.
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class PageTest extends TestCase
{
    /**
     * Test subject.
     *
     * @var Page
     */
    private $model;

    /**
     * @var Context|\PHPUnit\Framework\MockObject\MockObject
     */
    private $context;

    /**
     * @var MetadataPool|\PHPUnit\Framework\MockObject\MockObject
     */
    private $metadataPool;

    /**
     * @var EntityManager|\PHPUnit\Framework\MockObject\MockObject
     */
    private $entityManager;

    /**
     * @var GetUtilityPageIdentifiers|\PHPUnit\Framework\MockObject\MockObject
     */
    private $getUtilityPageIdentifiers;

    /**
     * @var ResourceConnection|\PHPUnit\Framework\MockObject\MockObject
     */
    private $resource;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $this->resource = $this->getMockBuilder(ResourceConnection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->context = $objectManager->getObject(
            Context::class,
            ['resource' => $this->resource]
        );
        $this->metadataPool = $this->getMockBuilder(MetadataPool::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->entityManager = $this->getMockBuilder(EntityManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->getUtilityPageIdentifiers = $this->getMockBuilder(GetUtilityPageIdentifiers::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->model = $objectManager->getObject(
            Page::class,
            [
                'context' => $this->context,
                'metadataPool' => $this->metadataPool,
                'entityManager' => $this->entityManager,
                'connectionName' => 'default',
                'getUtilityPageIdentifiers' => $this->getUtilityPageIdentifiers,
            ]
        );
    }

    /**
     * Test Page::getCollection() build correct query to get cms page collection array.
     *
     * @return void
     */
    public function testGetCollection()
    {
        $pageId = 'testPageId';
        $url = 'testUrl';
        $updatedAt = 'testUpdatedAt';
        $pageIdentifiers = ['testCmsHomePage', 'testCmsNoRoute', 'testCmsNoCookies'];
        $storeId = 1;
        $linkField = 'testLinkField';
        $expectedPage = new DataObject();
        $expectedPage->setId($pageId);
        $expectedPage->setUrl($url);
        $expectedPage->setUpdatedAt($updatedAt);

        $query = $this->getMockBuilder(\Zend_Db_Statement_Interface::class)
            ->disableOriginalConstructor()
            ->setMethods(['fetch'])
            ->getMockForAbstractClass();
        $query->expects($this->exactly(2))
            ->method('fetch')
            ->willReturnOnConsecutiveCalls(
                [
                    'page_id' => $pageId,
                    'url' => $url,
                    'updated_at' => $updatedAt
                ],
                false
            );

        $select = $this->getMockBuilder(Select::class)
            ->disableOriginalConstructor()
            ->getMock();
        $select->expects($this->once())
            ->method('from')
            ->with(
                $this->identicalTo(['main_table' => 'cms_page']),
                $this->identicalTo(['page_id', 'url' => 'identifier', 'updated_at' => 'update_time'])
            )->willReturnSelf();
        $select->expects($this->once())
            ->method('join')
            ->with(
                $this->identicalTo(['store_table' => 'cms_page_store']),
                $this->identicalTo("main_table.{$linkField} = store_table.$linkField"),
                $this->identicalTo([])
            )->willReturnSelf();
        $select->expects($this->exactly(3))
            ->method('where')
            ->withConsecutive(
                [$this->identicalTo('main_table.is_active = 1')],
                [$this->identicalTo('main_table.identifier NOT IN (?)'), $this->identicalTo($pageIdentifiers)],
                [$this->identicalTo('store_table.store_id IN(?)'), $this->identicalTo([0, $storeId])]
            )->willReturnSelf();

        $connection = $this->getMockBuilder(AdapterInterface::class)
            ->setMethods(['select'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $connection->expects($this->once())
            ->method('select')
            ->willReturn($select);
        $connection->expects($this->once())
            ->method('query')
            ->with($this->identicalTo($select))
            ->willReturn($query);

        $entityMetadata = $this->getMockBuilder(EntityMetadataInterface::class)
            ->setMethods(['getLinkField', 'getEntityConnection'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $entityMetadata->expects($this->once())
            ->method('getLinkField')
            ->willReturn($linkField);
        $entityMetadata->expects($this->exactly(2))
            ->method('getEntityConnection')
            ->willReturn($connection);

        $this->getUtilityPageIdentifiers->expects($this->once())
            ->method('execute')
            ->willReturn($pageIdentifiers);

        $this->resource->expects($this->exactly(2))
            ->method('getTableName')
            ->withConsecutive(
                [$this->identicalTo('cms_page'), $this->identicalTo('default')],
                [$this->identicalTo('cms_page_store'), $this->identicalTo('default')]
            )->willReturnOnConsecutiveCalls(
                'cms_page',
                'cms_page_store'
            );

        $this->metadataPool->expects($this->exactly(3))
            ->method('getMetadata')
            ->with($this->identicalTo(PageInterface::class))
            ->willReturn($entityMetadata);

        $result = $this->model->getCollection($storeId);
        $resultPage = array_shift($result);
        $this->assertEquals($expectedPage, $resultPage);
    }
}
