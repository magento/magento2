<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model\Indexer\Product\Eav\Plugin\AttributeSet;

use Magento\Catalog\Model\Indexer\Product\Eav\Plugin\AttributeSet\IndexableAttributeFilter;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\Catalog\Model\ResourceModel\Eav\AttributeFactory;
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
        $catalogResourceMock = $this->getMockBuilder(Attribute::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['load', 'isIndexable'])
            ->getMock();
        $catalogResourceMock->expects($this->any())
            ->method('load')
            ->willReturnSelf();
        $catalogResourceMock
            ->method('isIndexable')
            ->willReturnOnConsecutiveCalls(true, false);

        $eavAttributeFactoryMock = $this->getMockBuilder(AttributeFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();
        $eavAttributeFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($catalogResourceMock);

        $attributeMock1 = $this->getMockBuilder(\Magento\Eav\Model\Entity\Attribute::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getId', 'getAttributeId', 'getAttributeCode', 'load'])
            ->getMock();
        $attributeMock1->expects($this->any())
            ->method('getAttributeCode')
            ->willReturn('indexable_attribute');
        $attributeMock1->expects($this->any())
            ->method('load')
            ->willReturnSelf();

        $attributeMock2 = $this->getMockBuilder(\Magento\Eav\Model\Entity\Attribute::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getId', 'getAttributeId', 'getAttributeCode', 'load'])
            ->getMock();
        $attributeMock2->expects($this->any())
            ->method('getAttributeCode')
            ->willReturn('non_indexable_attribute');
        $attributeMock2->expects($this->any())
            ->method('load')
            ->willReturnSelf();

        $attributes = [$attributeMock1, $attributeMock2];

        $groupMock = $this->getMockBuilder(Group::class)
            ->disableOriginalConstructor()
            ->addMethods(['getAttributes'])
            ->getMock();
        $groupMock->expects($this->once())
            ->method('getAttributes')
            ->willReturn($attributes);

        $attributeSetMock = $this->getMockBuilder(Set::class)
            ->disableOriginalConstructor()
            ->addMethods(['getGroups'])
            ->getMock();
        $attributeSetMock->expects($this->once())
            ->method('getGroups')
            ->willReturn([$groupMock]);

        $model = new IndexableAttributeFilter(
            $eavAttributeFactoryMock
        );

        $this->assertEquals(['indexable_attribute'], $model->filter($attributeSetMock));
    }
}
