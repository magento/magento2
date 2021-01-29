<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogUrlRewrite\Test\Unit\Service\V1;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class StoreViewServiceTest extends \PHPUnit\Framework\TestCase
{
    /** @var \Magento\CatalogUrlRewrite\Service\V1\StoreViewService */
    protected $storeViewService;

    /** @var \Magento\Eav\Model\Config|\PHPUnit\Framework\MockObject\MockObject */
    protected $config;

    /** @var \Magento\Framework\App\ResourceConnection|\PHPUnit\Framework\MockObject\MockObject */
    protected $resource;

    /** @var \Magento\Framework\DB\Adapter\AdapterInterface|\PHPUnit\Framework\MockObject\MockObject */
    protected $connection;

    /** @var  \Magento\Framework\Db\Select|\PHPUnit\Framework\MockObject\MockObject */
    protected $select;

    protected function setUp(): void
    {
        $this->config = $this->createMock(\Magento\Eav\Model\Config::class);
        $this->select = $this->getMockBuilder(\Magento\Framework\DB\Select::class)
            ->setMethods(['select', 'from', 'where', 'join'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->connection = $this->getMockBuilder(\Magento\Framework\DB\Adapter\AdapterInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->resource = $this->createMock(\Magento\Framework\App\ResourceConnection::class);
        $this->resource->expects($this->any())->method('getConnection')->willReturn($this->connection);

        $this->storeViewService = (new ObjectManager($this))->getObject(
            \Magento\CatalogUrlRewrite\Service\V1\StoreViewService::class,
            [
                'eavConfig' => $this->config,
                'resource' => $this->resource,
            ]
        );
    }

    /**
     * @return array
     */
    public function isRootCategoryForStoreDataProvider()
    {
        return [
            [1, 1, 1, true],
            [1, 2, 1, false],
            [2, 0, 1, false],
        ];
    }

    /**
     * @return array
     */
    public function overriddenUrlKeyForStoreDataProvider()
    {
        return [
            [1, [1, 2], true],
            [1, [2, 3], false],
            [1, [], false],
        ];
    }

    /**
     * @dataProvider overriddenUrlKeyForStoreDataProvider
     * @param int $storeId
     * @param array $fetchedStoreIds
     * @param bool $result
     */
    public function testDoesEntityHaveOverriddenUrlKeyForStore($storeId, $fetchedStoreIds, $result)
    {
        $entityType = 'entity_type';
        $productId = 'product_id';
        $attribute = $this->getMockBuilder(\Magento\Eav\Model\Entity\Attribute\AbstractAttribute::class)
            ->disableOriginalConstructor()
            ->setMethods(['__wakeup', 'getBackendTable', 'getId', 'getEntity'])
            ->getMockForAbstractClass();
        $this->config->expects($this->once())->method('getAttribute')->with($entityType, 'url_key')
            ->willReturn($attribute);
        $entity = $this->getMockBuilder(\Magento\Eav\Model\Entity\AbstractEntity::class)
            ->disableOriginalConstructor()
            ->getMock();
        $attribute->expects($this->exactly(2))->method('getEntity')->willReturn($entity);
        $entity->expects($this->once())->method('getEntityTable')->willReturn('entity_table');
        $entity->expects($this->once())->method('getLinkField')->willReturn('link_field');
        $attribute->expects($this->once())->method('getBackendTable')->willReturn('backend_table');
        $attribute->expects($this->once())->method('getId')->willReturn('attribute-id');
        $this->select->expects($this->once())->method('from')->with(['e' => 'entity_table'], [])
            ->willReturnSelf();
        $this->select->expects($this->any())->method('where')->willReturnSelf();
        $this->select->expects($this->once())->method('join')->with(
            ['e_attr' => 'backend_table'],
            "e.link_field = e_attr.link_field",
            'store_id'
        )->willReturnSelf();
        $this->connection->expects($this->once())->method('select')->willReturn($this->select);
        $this->connection->expects($this->once())->method('fetchCol')->willReturn($fetchedStoreIds);

        $this->assertEquals(
            $result,
            $this->storeViewService->doesEntityHaveOverriddenUrlKeyForStore($storeId, $productId, $entityType)
        );
    }

    /**
     */
    public function testInvalidAttributeRetrieve()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Cannot retrieve attribute for entity type "invalid_type"');

        $invalidEntityType = 'invalid_type';
        $this->config->expects($this->once())->method('getAttribute')->willReturn(false);

        $this->storeViewService->doesEntityHaveOverriddenUrlKeyForStore(1, 1, $invalidEntityType);
    }
}
