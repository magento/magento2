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
namespace Magento\Catalog\Service\V1\Product\AttributeSet;

class AttributeServiceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var AttributeService
     */
    private $service;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $attributeMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $attributeGroupMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $attributeSetMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $entityTypeConfigMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $attrResourceMock;

    /**
     * @var \Magento\TestFramework\Helper\ObjectManager
     */
    protected $objectHelper;

    /**
     * @SuppressWarnings(PHPMD.LongVariable)
     */
    protected function setUp()
    {
        $this->objectHelper = new \Magento\TestFramework\Helper\ObjectManager($this);

        $attributeFactoryMock = $this->getMock(
            '\Magento\Eav\Model\Entity\AttributeFactory', array('create'), array(), '', false
        );
        $setFactoryMock = $this->getMock(
            '\Magento\Eav\Model\Entity\Attribute\SetFactory', array('create'), array(), '', false
        );
        $groupFactoryMock = $this->getMock(
            '\Magento\Eav\Model\Entity\Attribute\GroupFactory', array('create'), array(), '', false
        );
        $entityTypeFactoryMock = $this->getMock(
            '\Magento\Eav\Model\ConfigFactory', array('create'), array(), '', false
        );

        $this->attributeMock = $this->getMock(
            '\Magento\Eav\Model\Entity\Attribute',
            array(
                'getId', 'setId', 'setEntityTypeId', 'setAttributeSetId', 'load',
                'setAttributeGroupId', 'setSortOrder', 'loadEntityAttributeIdBySet', '__sleep', '__wakeup'
            ),
            array(), '', false
        );
        $this->attributeGroupMock = $this->getMock(
            '\Magento\Eav\Model\Entity\Attribute\Group', array(), array(), '', false
        );
        $this->attributeSetMock = $this->getMock(
            '\Magento\Eav\Model\Entity\Attribute\Set', array(), array(), '', false
        );
        $this->entityTypeConfigMock = $this->getMock(
            '\Magento\Eav\Model\Config', array('getEntityType', 'getEntityTypeCode', 'getId', '__sleep', '__wakeup'),
            array(), '', false
        );
        $this->attrResourceMock = $this->getMock(
            '\Magento\Eav\Model\Resource\Entity\Attribute', array(), array(), '', false
        );

        $attributeFactoryMock->expects($this->any())->method('create')->will($this->returnValue($this->attributeMock));
        $setFactoryMock->expects($this->any())->method('create')->will($this->returnValue($this->attributeSetMock));
        $groupFactoryMock->expects($this->any())->method('create')->will($this->returnValue($this->attributeGroupMock));
        $entityTypeFactoryMock->expects($this->any())
            ->method('create')->will($this->returnValue($this->entityTypeConfigMock));

        $this->service = new AttributeService(
            $attributeFactoryMock,
            $groupFactoryMock,
            $setFactoryMock,
            $entityTypeFactoryMock,
            $this->attrResourceMock
        );
    }

    /**
     * @covers \Magento\Catalog\Service\V1\Product\AttributeSet\AttributeService::__construct
     * @covers \Magento\Catalog\Service\V1\Product\AttributeSet\AttributeService::addAttribute
     */
    public function testAddAttribute()
    {
        $data = [
            'attribute_id'       => 1,
            'attribute_group_id' => 1,
            'sort_order'         => 1
        ];
        $builder = $this->objectHelper->getObject('\Magento\Catalog\Service\V1\Data\Eav\AttributeSet\AttributeBuilder');
        $attributeDataObject = $builder->populateWithArray($data)->create();

        $objectMock = $this->getMock('\Magento\Framework\Object', array(), array(), '', false);
        $objectMock->expects($this->any())->method('getId')->will($this->returnValue(1));
        $objectMock->expects($this->any())->method('getData')->will($this->returnValue(1));

        $this->attributeSetMock->expects($this->once())->method('load')->will($this->returnValue($objectMock));
        $this->attributeGroupMock->expects($this->once())->method('load')->will($this->returnValue($objectMock));
        $this->attributeMock->expects($this->once())->method('load')->will($this->returnValue($objectMock));

        $this->entityTypeConfigMock->expects($this->once())->method('getEntityType')->will($this->returnSelf());
        $this->entityTypeConfigMock->expects($this->once())
            ->method('getEntityTypeCode')->will($this->returnValue(\Magento\Catalog\Model\Product::ENTITY));
        $this->entityTypeConfigMock->expects($this->once())->method('getId')->will($this->returnValue(4));

        $this->attributeMock->expects($this->once())->method('setId')->with(1);
        $this->attributeMock->expects($this->once())->method('setEntityTypeId')->with(4);
        $this->attributeMock->expects($this->once())->method('setAttributeSetId')->with(1);
        $this->attributeMock->expects($this->once())->method('setAttributeGroupId')->with(1);
        $this->attributeMock->expects($this->once())->method('setSortOrder')->with(1);
        $this->attributeMock->expects($this->once())
            ->method('loadEntityAttributeIdBySet')->will($this->returnValue($objectMock));
        $this->attrResourceMock->expects($this->once())->method('saveInSetIncluding')->with($this->attributeMock);

        $this->service->addAttribute(1, $attributeDataObject);
    }

    /**
     * @covers \Magento\Catalog\Service\V1\Product\AttributeSet\AttributeService::addAttribute
     * @expectedException \Magento\Framework\Exception\InputException
     * @expectedExceptionMessage Attribute set does not exist
     */
    public function testAddAttributeWithWrongAttributeSet()
    {
        $builder = $this->objectHelper->getObject('Magento\Catalog\Service\V1\Data\Eav\AttributeSet\AttributeBuilder');
        $attributeDataObject = $builder->populateWithArray([])->create();

        $objectMock = $this->getMock('\Magento\Framework\Object', array(), array(), '', false);
        $this->attributeSetMock->expects($this->once())->method('load')->will($this->returnValue($objectMock));
        $this->service->addAttribute(1, $attributeDataObject);
    }

    /**
     * @covers \Magento\Catalog\Service\V1\Product\AttributeSet\AttributeService::addAttribute
     * @expectedException \Magento\Framework\Exception\InputException
     * @expectedExceptionMessage Wrong attribute set id provided
     */
    public function testAddAttributeWithAttributeSetOfOtherEntityType()
    {
        $builder = $this->objectHelper->getObject('Magento\Catalog\Service\V1\Data\Eav\AttributeSet\AttributeBuilder');
        $attributeDataObject = $builder->populateWithArray(['attribute_group' => 0])->create();

        $attributeSetMock = $this->getMock('\Magento\Framework\Object', array(), array(), '', false);
        $attributeSetMock->expects($this->any())->method('getId')->will($this->returnValue(1));
        $this->attributeSetMock->expects($this->once())->method('load')->will($this->returnValue($attributeSetMock));

        $this->entityTypeConfigMock->expects($this->once())->method('getEntityType')->will($this->returnSelf());
        $this->entityTypeConfigMock->expects($this->once())->method('getEntityTypeCode')->will($this->returnValue('0'));

        $this->service->addAttribute(1, $attributeDataObject);
    }

    /**
     * @covers \Magento\Catalog\Service\V1\Product\AttributeSet\AttributeService::addAttribute
     * @expectedException \Magento\Framework\Exception\InputException
     * @expectedExceptionMessage Attribute group does not exist
     */
    public function testAddAttributeWithWrongAttributeGroup()
    {
        $builder = $this->objectHelper->getObject('Magento\Catalog\Service\V1\Data\Eav\AttributeSet\AttributeBuilder');
        $attributeDataObject = $builder->populateWithArray(['attribute_group' => 0])->create();

        $attributeSetMock = $this->getMock('\Magento\Framework\Object', array(), array(), '', false);
        $attributeSetMock->expects($this->any())->method('getId')->will($this->returnValue(1));
        $this->attributeSetMock->expects($this->once())->method('load')->will($this->returnValue($attributeSetMock));

        $entityCode = \Magento\Catalog\Model\Product::ENTITY;
        $this->entityTypeConfigMock->expects($this->once())->method('getEntityType')->will($this->returnSelf());
        $this->entityTypeConfigMock->expects($this->once())
            ->method('getEntityTypeCode')->will($this->returnValue($entityCode));

        $attributeGroupMock = $this->getMock('\Magento\Framework\Object', array(), array(), '', false);
        $this->attributeGroupMock->expects($this->once())->method('load')
            ->will($this->returnValue($attributeGroupMock));

        $this->service->addAttribute(1, $attributeDataObject);
    }

    /**
     * @covers \Magento\Catalog\Service\V1\Product\AttributeSet\AttributeService::addAttribute
     * @expectedException \Magento\Framework\Exception\InputException
     * @expectedExceptionMessage Attribute does not exist
     */
    public function testAddAttributeWithWrongAttribute()
    {
        $builder = $this->objectHelper->getObject('Magento\Catalog\Service\V1\Data\Eav\AttributeSet\AttributeBuilder');
        $attributeDataObject = $builder->populateWithArray(['attribute_group' => 0])->create();

        $objectMock = $this->getMock('\Magento\Framework\Object', array(), array(), '', false);
        $objectMock->expects($this->any())->method('getId')->will($this->returnValue(1));
        $this->attributeSetMock->expects($this->once())->method('load')->will($this->returnValue($objectMock));
        $this->attributeGroupMock->expects($this->once())->method('load') ->will($this->returnValue($objectMock));

        $entityCode = \Magento\Catalog\Model\Product::ENTITY;
        $this->entityTypeConfigMock->expects($this->once())->method('getEntityType')->will($this->returnSelf());
        $this->entityTypeConfigMock->expects($this->once())
            ->method('getEntityTypeCode')->will($this->returnValue($entityCode));

        $attributeMock = $this->getMock('\Magento\Framework\Object', array(), array(), '', false);
        $this->attributeMock->expects($this->once())->method('load')->will($this->returnValue($attributeMock));

        $this->service->addAttribute(1, $attributeDataObject);
    }

    public function testSuccessfullyDeleteAttribute()
    {
        $entityCode = \Magento\Catalog\Model\Product::ENTITY;
        $methods = array('__wakeup', 'setAttributeSetId',
            'loadEntityAttributeIdBySet', 'getEntityAttributeId', 'deleteEntity', 'getId', 'getIsUserDefined');
        $this->entityTypeConfigMock->expects($this->once())->method('getEntityType')->will($this->returnSelf());
        $this->entityTypeConfigMock->expects($this->once())
            ->method('getEntityTypeCode')->will($this->returnValue($entityCode));
        $objectMock = $this->getMock('\Magento\Framework\Object', array(), array(), '', false);
        $objectMock->expects($this->any())->method('getId')->will($this->returnValue(1));
        $this->attributeSetMock->expects($this->once())->method('load')->with(1)->will($this->returnValue($objectMock));
        $attributeMock =
            $this->getMock('Magento\Eav\Model\Entity\Attribute\AbstractAttribute', $methods, array(), '', false);
        $this->attributeMock
            ->expects($this->once())->method('load')->with(10)->will($this->returnValue($attributeMock));
        $attributeMock->expects($this->any())->method('getId')->will($this->returnValue(2));
        $attributeMock->expects($this->once())->method('setAttributeSetId')->with(1)->will($this->returnSelf());
        $attributeMock->expects($this->once())->method('loadEntityAttributeIdBySet')->will($this->returnSelf());
        $attributeMock->expects($this->once())->method('getEntityAttributeId')->will($this->returnValue(10));
        $attributeMock->expects($this->once())->method('getIsUserDefined')->will($this->returnValue(true));
        $attributeMock->expects($this->once())->method('deleteEntity');
        $this->assertEquals(true, $this->service->deleteAttribute(1, 10));
    }

    /**
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @expectedExceptionMessage No such entity with attributeSetId = 1
     */
    public function testDeleteAttributeFromNonExistingAttributeSet()
    {
        $objectMock = $this->getMock('\Magento\Framework\Object', array(), array(), '', false);
        $objectMock->expects($this->any())->method('getId')->will($this->returnValue(false));
        $this->attributeSetMock->expects($this->once())->method('load')->will($this->returnValue($objectMock));
        $this->attributeMock->expects($this->never())->method('load');

        $this->service->deleteAttribute(1, 10);
    }

    /**
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @expectedExceptionMessage No such entity with attributeId = 10
     */
    public function testDeleteNonExistingAttribute()
    {
        $entityCode = \Magento\Catalog\Model\Product::ENTITY;
        $methods = array('__wakeup', 'setAttributeSetId',
            'loadEntityAttributeIdBySet', 'getEntityAttributeId', 'deleteEntity', 'getId');
        $this->entityTypeConfigMock->expects($this->once())->method('getEntityType')->will($this->returnSelf());
        $this->entityTypeConfigMock->expects($this->once())
            ->method('getEntityTypeCode')->will($this->returnValue($entityCode));
        $objectMock = $this->getMock('\Magento\Framework\Object', array(), array(), '', false);
        $objectMock->expects($this->any())->method('getId')->will($this->returnValue(1));
        $this->attributeSetMock->expects($this->once())->method('load')->with(1)->will($this->returnValue($objectMock));
        $attributeMock =
            $this->getMock('Magento\Eav\Model\Entity\Attribute\AbstractAttribute', $methods, array(), '', false);
        $this->attributeMock->expects($this->once())->method('load')->with(10)
            ->will($this->returnValue($attributeMock));
        $attributeMock->expects($this->any())->method('getId')->will($this->returnValue(false));
        $attributeMock->expects($this->never())->method('setAttributeSetId');
        $this->service->deleteAttribute(1, 10);
    }

    /**
     * @expectedException \Magento\Framework\Exception\InputException
     * @expectedExceptionMessage Requested attribute is not in requested attribute set.
     */
    public function testDeleteAttributeNotInAttributeSet()
    {
        $entityCode = \Magento\Catalog\Model\Product::ENTITY;
        $methods = array('__wakeup', 'setAttributeSetId',
            'loadEntityAttributeIdBySet', 'getEntityAttributeId', 'deleteEntity', 'getId');
        $this->entityTypeConfigMock->expects($this->once())->method('getEntityType')->will($this->returnSelf());
        $this->entityTypeConfigMock->expects($this->once())
            ->method('getEntityTypeCode')->will($this->returnValue($entityCode));
        $objectMock = $this->getMock('\Magento\Framework\Object', array(), array(), '', false);
        $objectMock->expects($this->any())->method('getId')->will($this->returnValue(1));
        $this->attributeSetMock->expects($this->once())->method('load')->with(1)->will($this->returnValue($objectMock));
        $attributeMock =
            $this->getMock('Magento\Eav\Model\Entity\Attribute\AbstractAttribute', $methods, array(), '', false);
        $this->attributeMock->expects($this->once())->method('load')->with(10)
            ->will($this->returnValue($attributeMock));
        $attributeMock->expects($this->any())->method('getId')->will($this->returnValue(2));
        $attributeMock->expects($this->once())->method('setAttributeSetId')->with(1)->will($this->returnSelf());
        $attributeMock->expects($this->once())->method('loadEntityAttributeIdBySet')->will($this->returnSelf());
        $attributeMock->expects($this->once())->method('getEntityAttributeId')->will($this->returnValue(false));
        $attributeMock->expects($this->never())->method('deleteEntity');
        $this->assertEquals(true, $this->service->deleteAttribute(1, 10));
    }

    /**
     * @expectedException \Magento\Framework\Exception\InputException
     * @expectedExceptionMessage Attribute with wrong attribute type is provided
     */
    public function testDeleteAttributeHasNotCorrespondingType()
    {
        $this->entityTypeConfigMock->expects($this->once())->method('getEntityType')->will($this->returnSelf());
        $this->entityTypeConfigMock->expects($this->once())
            ->method('getEntityTypeCode')->will($this->returnValue('some_type'));
        $objectMock = $this->getMock('\Magento\Framework\Object', array(), array(), '', false);
        $objectMock->expects($this->any())->method('getId')->will($this->returnValue(1));
        $this->attributeSetMock->expects($this->once())->method('load')->will($this->returnValue($objectMock));
        $this->attributeMock->expects($this->never())->method('load');
        $this->assertEquals(true, $this->service->deleteAttribute(1, 10));
    }

    /**
     * @expectedException \Magento\Framework\Exception\StateException
     * @expectedExceptionMessage System attribute can not be deleted
     */
    public function testDeleteSystemAttribute()
    {
        $entityCode = \Magento\Catalog\Model\Product::ENTITY;
        $methods = array('__wakeup', 'setAttributeSetId',
            'loadEntityAttributeIdBySet', 'getEntityAttributeId', 'deleteEntity', 'getId', 'getIsUserDefined');
        $this->entityTypeConfigMock->expects($this->once())->method('getEntityType')->will($this->returnSelf());
        $this->entityTypeConfigMock->expects($this->once())
            ->method('getEntityTypeCode')->will($this->returnValue($entityCode));
        $objectMock = $this->getMock('\Magento\Framework\Object', array(), array(), '', false);
        $objectMock->expects($this->any())->method('getId')->will($this->returnValue(1));
        $this->attributeSetMock->expects($this->once())->method('load')->with(1)->will($this->returnValue($objectMock));
        $attributeMock =
            $this->getMock('Magento\Eav\Model\Entity\Attribute\AbstractAttribute', $methods, array(), '', false);
        $this->attributeMock
            ->expects($this->once())->method('load')->with(10)->will($this->returnValue($attributeMock));
        $attributeMock->expects($this->any())->method('getId')->will($this->returnValue(2));
        $attributeMock->expects($this->once())->method('setAttributeSetId')->with(1)->will($this->returnSelf());
        $attributeMock->expects($this->once())->method('loadEntityAttributeIdBySet')->will($this->returnSelf());
        $attributeMock->expects($this->once())->method('getEntityAttributeId')->will($this->returnValue(10));
        $attributeMock->expects($this->once())->method('getIsUserDefined')->will($this->returnValue(false));
        $attributeMock->expects($this->never())->method('deleteEntity');
        $this->service->deleteAttribute(1, 10);
    }

}
