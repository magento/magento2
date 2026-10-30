<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model\Indexer\Product\Eav\Plugin\AttributeSet;

use Magento\Catalog\Model\Indexer\Product\Eav\Plugin\AttributeSet\IndexableAttributeFilter;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\Catalog\Model\ResourceModel\Eav\AttributeFactory;
use Magento\Eav\Model\Entity\Attribute as EntityAttribute;
use Magento\Eav\Model\Entity\Attribute\Group;
use Magento\Eav\Model\Entity\Attribute\Set;
use PHPUnit\Framework\TestCase;

class IndexableAttributeFilterTest extends TestCase
{
    /**
     * @return void
     */
    public function testFilter(): void
    {
        $catalogResourceMock = $this->createPartialMock(Attribute::class, ['load', 'isIndexable']);
        $catalogResourceMock->expects($this->any())
            ->method('load')
            ->willReturnSelf();
        $catalogResourceMock
            ->method('isIndexable')
            ->willReturnOnConsecutiveCalls(true, false);

        $eavAttributeFactoryMock = $this->createPartialMock(AttributeFactory::class, ['create']);
        $eavAttributeFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($catalogResourceMock);

        $attributeMock1 = $this->createPartialMock(
            EntityAttribute::class,
            ['getId', 'getAttributeId', 'getAttributeCode', 'load']
        );
        $attributeMock1->method('getAttributeCode')->willReturn('indexable_attribute');
        $attributeMock1->expects($this->any())
            ->method('load')
            ->willReturnSelf();

        $attributeMock2 = $this->createPartialMock(
            EntityAttribute::class,
            ['getId', 'getAttributeId', 'getAttributeCode', 'load']
        );
        $attributeMock2->method('getAttributeCode')->willReturn('non_indexable_attribute');
        $attributeMock2->expects($this->any())
            ->method('load')
            ->willReturnSelf();

        $attributes = [$attributeMock1, $attributeMock2];

        $groupMock = $this->createPartialMock(Group::class, []);
        $groupMock->setData('attributes', $attributes);

        $attributeSetMock = $this->createPartialMock(Set::class, []);
        $attributeSetMock->setData('groups', [$groupMock]);

        $model = new IndexableAttributeFilter(
            $eavAttributeFactoryMock
        );

        $this->assertEquals(['indexable_attribute'], $model->filter($attributeSetMock));
    }
}
