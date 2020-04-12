<?php declare(strict_types=1);
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Model\Indexer\Product\Eav\Plugin\AttributeSet;

use Magento\Catalog\Model\Indexer\Product\Eav\Plugin\AttributeSet\IndexableAttributeFilter;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\Catalog\Model\ResourceModel\Eav\AttributeFactory;
use Magento\Eav\Model\Entity\Attribute\Group;
use Magento\Eav\Model\Entity\Attribute\Set;
use PHPUnit\Framework\TestCase;

class IndexableAttributeFilterTest extends TestCase
{
    public function testFilter()
    {
        $catalogResourceMock = $this->getMockBuilder(Attribute::class)
            ->disableOriginalConstructor()
            ->setMethods(['load', 'isIndexable', '__wakeup'])
            ->getMock();
        $catalogResourceMock->expects($this->any())
            ->method('load')
            ->will($this->returnSelf());
        $catalogResourceMock->expects($this->at(1))
            ->method('isIndexable')
            ->will($this->returnValue(true));
        $catalogResourceMock->expects($this->at(2))
            ->method('isIndexable')
            ->will($this->returnValue(false));

        $eavAttributeFactoryMock = $this->getMockBuilder(
            AttributeFactory::class
        )
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $eavAttributeFactoryMock->expects($this->once())
            ->method('create')
            ->will($this->returnValue($catalogResourceMock));

        $attributeMock1 = $this->getMockBuilder(\Magento\Eav\Model\Entity\Attribute::class)
            ->disableOriginalConstructor()
            ->setMethods(['getId', 'getAttributeId', 'getAttributeCode', 'load', '__wakeup'])
            ->getMock();
        $attributeMock1->expects($this->any())
            ->method('getAttributeCode')
            ->will($this->returnValue('indexable_attribute'));
        $attributeMock1->expects($this->any())
            ->method('load')
            ->will($this->returnSelf());

        $attributeMock2 = $this->getMockBuilder(\Magento\Eav\Model\Entity\Attribute::class)
            ->disableOriginalConstructor()
            ->setMethods(['getId', 'getAttributeId', 'getAttributeCode', 'load', '__wakeup'])
            ->getMock();
        $attributeMock2->expects($this->any())
            ->method('getAttributeCode')
            ->will($this->returnValue('non_indexable_attribute'));
        $attributeMock2->expects($this->any())
            ->method('load')
            ->will($this->returnSelf());

        $attributes = [$attributeMock1, $attributeMock2];

        $groupMock = $this->getMockBuilder(Group::class)
            ->disableOriginalConstructor()
            ->setMethods(['getAttributes', '__wakeup'])
            ->getMock();
        $groupMock->expects($this->once())
            ->method('getAttributes')
            ->will($this->returnValue($attributes));

        $attributeSetMock = $this->getMockBuilder(Set::class)
            ->disableOriginalConstructor()
            ->setMethods(['getGroups', '__wakeup'])
            ->getMock();
        $attributeSetMock->expects($this->once())
            ->method('getGroups')
            ->will($this->returnValue([$groupMock]));

        $model = new IndexableAttributeFilter(
            $eavAttributeFactoryMock
        );

        $this->assertEquals(['indexable_attribute'], $model->filter($attributeSetMock));
    }
}
