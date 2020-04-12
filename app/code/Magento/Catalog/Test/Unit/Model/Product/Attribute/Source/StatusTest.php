<?php declare(strict_types=1);
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Unit\Model\Product\Attribute\Source;

use Magento\Catalog\Model\Entity\Attribute;
use Magento\Catalog\Model\Product\Attribute\Backend\Sku;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Eav\Model\Entity\AbstractEntity;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Eav\Model\Entity\Attribute\Backend\AbstractBackend;
use Magento\Eav\Model\Entity\Collection\AbstractCollection;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class StatusTest extends TestCase
{
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
        $this->collection = $this->createPartialMock(
            Collection::class,
            [
                '__wakeup',
                'getSelect',
                'joinLeft',
                'order',
                'getStoreId',
                'getConnection',
                'getCheckSql'
            ]
        );
        $this->attributeModel = $this->createPartialMock(
            Attribute::class,
            [
                '__wakeup',
                'getAttributeCode',
                'getBackend',
                'getId',
                'isScopeGlobal',
                'getEntity',
                'getAttribute'
            ]
        );
        $this->backendAttributeModel = $this->createPartialMock(
            Sku::class,
            ['__wakeup', 'getTable']
        );
        $this->status = $this->objectManagerHelper->getObject(
            Status::class
        );

        $this->attributeModel->expects($this->any())->method('getAttribute')
            ->will($this->returnSelf());
        $this->attributeModel->expects($this->any())->method('getAttributeCode')
            ->will($this->returnValue('attribute_code'));
        $this->attributeModel->expects($this->any())->method('getId')
            ->will($this->returnValue('1'));
        $this->attributeModel->expects($this->any())->method('getBackend')
            ->will($this->returnValue($this->backendAttributeModel));
        $this->collection->expects($this->any())->method('getSelect')
            ->will($this->returnSelf());
        $this->collection->expects($this->any())->method('joinLeft')
            ->will($this->returnSelf());
        $this->backendAttributeModel->expects($this->any())->method('getTable')
            ->will($this->returnValue('table_name'));

        $this->entity = $this->getMockBuilder(AbstractEntity::class)
            ->disableOriginalConstructor()
            ->setMethods(['getLinkField'])
            ->getMockForAbstractClass();
    }

    public function testAddValueSortToCollectionGlobal()
    {
        $this->attributeModel->expects($this->any())->method('isScopeGlobal')
            ->will($this->returnValue(true));
        $this->collection->expects($this->once())->method('order')->with('attribute_code_t.value asc')
            ->will($this->returnSelf());

        $this->attributeModel->expects($this->once())->method('getEntity')->willReturn($this->entity);
        $this->entity->expects($this->once())->method('getLinkField')->willReturn('entity_id');

        $this->status->setAttribute($this->attributeModel);
        $this->status->addValueSortToCollection($this->collection);
    }

    public function testAddValueSortToCollectionNotGlobal()
    {
        $this->attributeModel->expects($this->any())->method('isScopeGlobal')
            ->will($this->returnValue(false));

        $this->collection->expects($this->once())->method('order')->with('check_sql asc')
            ->will($this->returnSelf());
        $this->collection->expects($this->once())->method('getStoreId')
            ->will($this->returnValue(1));
        $this->collection->expects($this->any())->method('getConnection')
            ->will($this->returnSelf());
        $this->collection->expects($this->any())->method('getCheckSql')
            ->will($this->returnValue('check_sql'));

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
     * @dataProvider getOptionTextDataProvider
     * @param string $text
     * @param string $id
     */
    public function testGetOptionText($text, $id)
    {
        $this->assertEquals($text, $this->status->getOptionText($id));
    }

    /**
     * @return array
     */
    public function getOptionTextDataProvider()
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
