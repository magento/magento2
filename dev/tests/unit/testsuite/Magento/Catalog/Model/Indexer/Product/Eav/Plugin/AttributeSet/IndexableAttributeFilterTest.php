<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Catalog\Model\Indexer\Product\Eav\Plugin\AttributeSet;

class IndexableAttributeFilterTest extends \PHPUnit_Framework_TestCase
{
    public function testFilter()
    {
        $catalogResourceMock = $this->getMockBuilder('Magento\Catalog\Model\Resource\Eav\Attribute')
            ->disableOriginalConstructor()
            ->setMethods(array('load', 'isIndexable', '__wakeup'))
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

        $eavAttributeFactoryMock = $this->getMockBuilder('Magento\Catalog\Model\Resource\Eav\AttributeFactory')
            ->disableOriginalConstructor()
            ->setMethods(array('create'))
            ->getMock();
        $eavAttributeFactoryMock->expects($this->once())
            ->method('create')
            ->will($this->returnValue($catalogResourceMock));

        $attributeMock1 = $this->getMockBuilder('Magento\Eav\Model\Entity\Attribute')
            ->disableOriginalConstructor()
            ->setMethods(array('getId', 'getAttributeId', 'getAttributeCode', 'load', '__wakeup'))
            ->getMock();
        $attributeMock1->expects($this->any())
            ->method('getAttributeCode')
            ->will($this->returnValue('indexable_attribute'));
        $attributeMock1->expects($this->any())
            ->method('load')
            ->will($this->returnSelf());

        $attributeMock2 = $this->getMockBuilder('Magento\Eav\Model\Entity\Attribute')
            ->disableOriginalConstructor()
            ->setMethods(array('getId', 'getAttributeId', 'getAttributeCode', 'load', '__wakeup'))
            ->getMock();
        $attributeMock2->expects($this->any())
            ->method('getAttributeCode')
            ->will($this->returnValue('non_indexable_attribute'));
        $attributeMock2->expects($this->any())
            ->method('load')
            ->will($this->returnSelf());

        $attributes = array($attributeMock1, $attributeMock2);

        $groupMock = $this->getMockBuilder('Magento\Eav\Model\Entity\Attribute\Group')
            ->disableOriginalConstructor()
            ->setMethods(array('getAttributes', '__wakeup'))
            ->getMock();
        $groupMock->expects($this->once())
            ->method('getAttributes')
            ->will($this->returnValue($attributes));

        $attributeSetMock = $this->getMockBuilder('Magento\Eav\Model\Entity\Attribute\Set')
            ->disableOriginalConstructor()
            ->setMethods(array('getGroups', '__wakeup'))
            ->getMock();
        $attributeSetMock->expects($this->once())
            ->method('getGroups')
            ->will($this->returnValue(array($groupMock)));

        $model = new \Magento\Catalog\Model\Indexer\Product\Eav\Plugin\AttributeSet\IndexableAttributeFilter(
            $eavAttributeFactoryMock
        );

        $this->assertEquals(array('indexable_attribute'), $model->filter($attributeSetMock));
    }
}
