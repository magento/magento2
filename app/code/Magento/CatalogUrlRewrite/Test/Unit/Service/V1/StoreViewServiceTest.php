<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CatalogUrlRewrite\Test\Unit\Service\V1;

use Magento\CatalogUrlRewrite\Service\V1\StoreViewService;
use Magento\Eav\Model\Config;
use Magento\Eav\Model\Entity\AbstractEntity;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select as DbSelect;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class StoreViewServiceTest extends TestCase
{
    use MockCreationTrait;

    /** @var StoreViewService */
    protected $storeViewService;

    /** @var Config|MockObject */
    protected $config;

    /** @var ResourceConnection|MockObject */
    protected $resource;

    /** @var AdapterInterface|MockObject */
    protected $connection;

    /** @var  DbSelect|MockObject */
    protected $select;

    protected function setUp(): void
    {
        $this->config = $this->createMock(Config::class);
        $this->select = $this->createPartialMockWithReflection(
            DbSelect::class,
            ['select', 'from', 'where', 'join']
        );
        $this->connection = $this->createMock(AdapterInterface::class);
        $this->resource = $this->createMock(ResourceConnection::class);
        $this->resource->method('getConnection')->willReturn($this->connection);

        $this->storeViewService = (new ObjectManager($this))->getObject(
            StoreViewService::class,
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
    public static function overriddenUrlKeyForStoreDataProvider()
    {
        return [
            [1, [1, 2], true],
            [1, [2, 3], false],
            [1, [], false],
        ];
    }

    /**
     * @param int $storeId
     * @param array $fetchedStoreIds
     * @param bool $result
     */
    #[DataProvider('overriddenUrlKeyForStoreDataProvider')]
    public function testDoesEntityHaveOverriddenUrlKeyForStore($storeId, $fetchedStoreIds, $result)
    {
        $entityType = 'entity_type';
        $productId = 'product_id';
        $attribute = $this->createPartialMock(
            AbstractAttribute::class,
            ['__wakeup', 'getBackendTable', 'getId', 'getEntity']
        );
        $this->config->expects($this->once())->method('getAttribute')->with($entityType, 'url_key')
            ->willReturn($attribute);
        $entity = $this->createMock(AbstractEntity::class);
        $attribute->expects($this->exactly(2))->method('getEntity')->willReturn($entity);
        $entity->expects($this->once())->method('getEntityTable')->willReturn('entity_table');
        $entity->expects($this->once())->method('getLinkField')->willReturn('link_field');
        $attribute->expects($this->once())->method('getBackendTable')->willReturn('backend_table');
        $attribute->expects($this->once())->method('getId')->willReturn('attribute-id');
        $this->select->expects($this->once())->method('from')->with(['e' => 'entity_table'], [])->willReturnSelf();
        $this->select->method('where')->willReturnSelf();
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

    public function testInvalidAttributeRetrieve()
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('Cannot retrieve attribute for entity type "invalid_type"');
        $invalidEntityType = 'invalid_type';
        $this->config->expects($this->once())->method('getAttribute')->willReturn(false);

        $this->storeViewService->doesEntityHaveOverriddenUrlKeyForStore(1, 1, $invalidEntityType);
    }
}
