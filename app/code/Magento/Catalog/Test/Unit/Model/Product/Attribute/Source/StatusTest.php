<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model\Product\Attribute\Source;

use PHPUnit\Framework\Attributes\DataProvider;
use Magento\Catalog\Model\Entity\Attribute;
use Magento\Catalog\Model\Product\Attribute\Backend\Sku;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Eav\Model\Entity\AbstractEntity;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Eav\Model\Entity\Attribute\Backend\AbstractBackend;
use Magento\Eav\Model\Entity\Collection\AbstractCollection;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class StatusTest extends TestCase
{
    use MockCreationTrait;
    /** @var Status */
    protected $status;

    /** @var ObjectManagerHelper */
    protected $objectManagerHelper;

    /** @var AbstractCollection|MockObject */
    protected $collection;

    /** @var AbstractAttribute|MockObject */
    protected $attributeModel;

    /** @var AbstractBackend|MockObject */
    protected $backendAttributeModel;

    /**
     * @var AbstractEntity|MockObject
     */
    protected $entity;

    protected function setUp(): void
    {
        $this->objectManagerHelper = new ObjectManagerHelper($this);
        
        $this->collection = $this->createPartialMockWithReflection(
            AbstractCollection::class,
            ['getSelect', 'getStoreId', 'getConnection', 'order', 'joinLeft', 'getCheckSql']
        );
        $this->collection->expects($this->any())->method('getSelect')->willReturnSelf();
        $this->collection->expects($this->any())->method('joinLeft')->willReturnSelf();
        $this->collection->expects($this->any())->method('getConnection')->willReturnSelf();
        
        $this->attributeModel = $this->createPartialMockWithReflection(
            AbstractAttribute::class,
            ['getAttributeCode', 'getId', 'getBackend', 'isScopeGlobal', 'getEntity', 'getAttribute']
        );
        
        $this->backendAttributeModel = $this->createPartialMock(Sku::class, ['getTable']);
        
        $this->status = $this->objectManagerHelper->getObject(Status::class);

        $this->attributeModel->expects($this->any())->method('getAttribute')->willReturnSelf();
        $this->attributeModel->expects($this->any())->method('getAttributeCode')->willReturn('attribute_code');
        $this->attributeModel->expects($this->any())->method('getId')->willReturn('1');
        $this->attributeModel->expects($this->any())->method('getBackend')->willReturn($this->backendAttributeModel);
        $this->backendAttributeModel->expects($this->any())->method('getTable')->willReturn('table_name');

        $this->entity = $this->createMock(AbstractEntity::class);
    }

    public function testAddValueSortToCollectionGlobal()
    {
        $this->attributeModel->expects($this->any())->method('isScopeGlobal')->willReturn(true);
        $this->collection->expects($this->once())->method('order')
            ->with('attribute_code_t.value asc')->willReturnSelf();
        
        $this->attributeModel->expects($this->once())->method('getEntity')->willReturn($this->entity);
        $this->entity->expects($this->once())->method('getLinkField')->willReturn('entity_id');

        $this->status->setAttribute($this->attributeModel);
        $this->status->addValueSortToCollection($this->collection);
    }

    public function testAddValueSortToCollectionNotGlobal()
    {
        $this->attributeModel->expects($this->any())->method('isScopeGlobal')->willReturn(false);
        
        $this->collection->expects($this->once())->method('order')->with('check_sql asc')->willReturnSelf();
        $this->collection->expects($this->once())->method('getStoreId')->willReturn(1);
        $this->collection->expects($this->any())->method('getCheckSql')->willReturn('check_sql');
        
        $this->attributeModel->expects($this->any())->method('getEntity')->willReturn($this->entity);
        $this->entity->expects($this->once())->method('getLinkField')->willReturn('entity_id');

        $this->status->setAttribute($this->attributeModel);
        $this->status->addValueSortToCollection($this->collection);
    }

    public function testGetVisibleStatusIds()
    {
        $this->assertEquals([0 => 1], $this->status->getVisibleStatusIds());
    }

    public function testGetSaleableStatusIds()
    {
        $this->assertEquals([0 => 1], $this->status->getSaleableStatusIds());
    }

    public function testGetOptionArray()
    {
        $this->assertEquals([1 => 'Enabled', 2 => 'Disabled'], $this->status->getOptionArray());
    }

    /**
     * @param string $text
     * @param string $id
     */
    #[DataProvider('getOptionTextDataProvider')]
    public function testGetOptionText($text, $id)
    {
        $this->assertEquals($text, $this->status->getOptionText($id));
    }

    /**
     * @return array
     */
    public static function getOptionTextDataProvider()
    {
        return [
            [
                'text' => 'Enabled',
                'id' => '1',
            ],
            [
                'text' => 'Disabled',
                'id' => '2'
            ]
        ];
    }
}
