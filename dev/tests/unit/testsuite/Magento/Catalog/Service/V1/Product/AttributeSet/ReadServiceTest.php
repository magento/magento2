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

class ReadServiceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Catalog\Service\V1\Product\AttributeSet\ReadService
     */
    protected $service;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $setFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $collectionFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $eavConfigMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $entityTypeMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $builderMock;


    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $attrCollectionMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $attributeBuilderMock;

    protected function setUp()
    {
        $this->setFactoryMock = $this->getMock('\Magento\Eav\Model\Entity\Attribute\SetFactory',
            array('create'), array(), '', false);
        $this->collectionFactoryMock = $this->getMock(
            '\Magento\Eav\Model\Resource\Entity\Attribute\Set\CollectionFactory',
            array('create'), array(), '', false
        );

        $this->eavConfigMock = $this->getMock('\Magento\Eav\Model\Config', array(), array(), '', false);
        $this->entityTypeMock = $this->getMock('\Magento\Eav\Model\Entity\Type', array(), array(), '', false);
        $this->eavConfigMock->expects($this->any())
            ->method('getEntityType')
            ->with(\Magento\Catalog\Model\Product::ENTITY)
            ->will($this->returnValue($this->entityTypeMock));

        $this->builderMock = $this->getMock('\Magento\Catalog\Service\V1\Data\Eav\AttributeSetBuilder',
            array('create', 'setId', 'setName', 'setSortOrder'), array(), '', false);

        $this->attrCollectionMock = $this->getMock('\Magento\Eav\Model\Resource\Entity\Attribute\Collection',
            array(), array(), '', false);
        $this->attributeBuilderMock = $this->getMock('\Magento\Catalog\Service\V1\Data\Eav\AttributeBuilder',
            array(), array(), '', false);

        $this->service = new ReadService(
            $this->setFactoryMock,
            $this->collectionFactoryMock,
            $this->eavConfigMock,
            $this->builderMock,
            $this->attrCollectionMock,
            $this->attributeBuilderMock
        );
    }

    public function testGetList()
    {
        $attributeSetData = array('attribute_set_id' => 4, 'attribute_set_name' => 'Default', 'sort_order' => 2);

        $collectionMock = $this->getMock(
            '\Magento\Eav\Model\Resource\Entity\Attribute\Set\Collection', array(), array(), '', false
        );

        $productEntityId = 4;
        $this->entityTypeMock->expects($this->once())->method('getId')->will($this->returnValue($productEntityId));

        $this->collectionFactoryMock->expects($this->once())->method('create')
            ->will($this->returnValue($collectionMock));

        $collectionMock->expects($this->once())->method('setEntityTypeFilter')
            ->with($productEntityId)
            ->will($this->returnSelf());

        $collectionMock->expects($this->once())->method('load')->will($this->returnSelf());

        $attributeSets = $resultSets = array();

        //prepare getter checks
        $setMock = $this->getMock('\Magento\Eav\Model\Resource\Entity\Attribute\Set',
            array('getId', 'getAttributeSetName', 'getSortOrder', '__wakeup'), array(), '', false);
        $setMock->expects($this->any())->method('getId')
            ->will($this->returnValue($attributeSetData['attribute_set_id']));
        $setMock->expects($this->any())->method('getAttributeSetName')
            ->will($this->returnValue($attributeSetData['attribute_set_name']));
        $setMock->expects($this->any())->method('getSortOrder')
            ->will($this->returnValue($attributeSetData['sort_order']));
        $attributeSets[] = $setMock;

        //prepare setter checks
        $this->builderMock->expects($this->once())->method('setId')
            ->with($attributeSetData['attribute_set_id']);
        $this->builderMock->expects($this->once())->method('setName')
            ->with($attributeSetData['attribute_set_name']);
        $this->builderMock->expects($this->once())->method('setSortOrder')
            ->with($attributeSetData['sort_order']);

        $dataObjectMock = $this->getMock(
            'Magento\Catalog\Service\V1\Data\Eav\AttributeSet', array(), array(), '', false
        );
        $this->builderMock->expects($this->once())->method('create')->will($this->returnValue($dataObjectMock));
        $resultSets[] = $dataObjectMock;

        $collectionMock->expects($this->any())->method('getIterator')
            ->will($this->returnValue(new \ArrayIterator($attributeSets)));

        $this->assertEquals($resultSets, $this->service->getList());
    }

    public function testGetInfoReturnsAttributeSetIfIdIsValid()
    {
        $attributeSetData = array(
            'id' => 4,
            'name' => 'Default',
            'sort_order' => 2,
        );
        $attributeSetMock = $this->getMock(
            '\Magento\Eav\Model\Entity\Attribute\Set',
            array('load', 'getId', 'getAttributeSetName', 'getSortOrder', '__wakeup', 'getEntityTypeId'),
            array(),
            '',
            false
        );
        $entityTypeId = 4;
        $this->entityTypeMock->expects($this->once())->method('getId')->will($this->returnValue($entityTypeId));

        $this->setFactoryMock->expects($this->once())->method('create')
            ->will($this->returnValue($attributeSetMock));
        $attributeSetMock->expects($this->any())->method('getId')
            ->will($this->returnValue($attributeSetData['id']));
        $attributeSetMock->expects($this->any())->method('getAttributeSetName')
            ->will($this->returnValue($attributeSetData['name']));
        $attributeSetMock->expects($this->any())->method('getSortOrder')
            ->will($this->returnValue($attributeSetData['sort_order']));
        $attributeSetMock->expects($this->any())
            ->method('getEntityTypeId')
            ->will($this->returnValue($entityTypeId));
        $attributeSetMock->expects($this->once())
            ->method('load')
            ->with($attributeSetData['id'])
            ->will($this->returnSelf());
        $this->builderMock->expects($this->once())
            ->method('setId')
            ->with($attributeSetData['id'])
            ->will($this->returnSelf());
        $this->builderMock->expects($this->once())
            ->method('setName')
            ->with($attributeSetData['name'])
            ->will($this->returnSelf());
        $this->builderMock->expects($this->once())
            ->method('setSortOrder')
            ->with($attributeSetData['sort_order'])
            ->will($this->returnSelf());
        $dataObjectMock = $this->getMock(
            '\Magento\Catalog\Service\V1\Data\Eav\AttributeSet',
            array(),
            array(),
            '',
            false
        );
        $this->builderMock->expects($this->once())
            ->method('create')
            ->will($this->returnValue($dataObjectMock));
        $this->assertEquals($dataObjectMock, $this->service->getInfo($attributeSetData['id']));
    }

    /**
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @expectedExceptionMessage No such entity with attributeSetId = 1
     */
    public function testGetInfoThrowsExceptionIfIdIsNotValid()
    {
        $attributeSetData = array(
            'id' => 1,
        );
        $attributeSetMock = $this->getMock(
            '\Magento\Eav\Model\Entity\Attribute\Set',
            array('load', 'getId', 'getName', 'getSortOrder', '__wakeup'),
            array(),
            '',
            false
        );
        $this->setFactoryMock->expects($this->once())->method('create')->will($this->returnValue($attributeSetMock));
        $attributeSetMock->expects($this->once())
            ->method('load')
            ->with($attributeSetData['id'])
            ->will($this->returnSelf());
        $this->builderMock->expects($this->never())->method('create');
        $this->service->getInfo($attributeSetData['id']);
    }

    public function testGetAttributeListIfIdIsValid()
    {
        $attributeSetId = 4;
        $attributeSetMock = $this->getMock(
            '\Magento\Eav\Model\Entity\Attribute\Set',
            array('load', 'getId', 'getAttributeSetName', 'getSortOrder', '__wakeup', 'getEntityTypeId'),
            array(),
            '',
            false
        );
        $entityTypeId = 4;
        $this->entityTypeMock->expects($this->once())->method('getId')->will($this->returnValue($entityTypeId));

        $this->setFactoryMock->expects($this->once())->method('create')
            ->will($this->returnValue($attributeSetMock));
        $attributeSetMock->expects($this->any())->method('getId')
            ->will($this->returnValue($attributeSetId));
        $attributeSetMock->expects($this->any())
            ->method('getEntityTypeId')
            ->will($this->returnValue($entityTypeId));
        $attributeSetMock->expects($this->once())
            ->method('load')
            ->with($attributeSetId)
            ->will($this->returnSelf());

        $this->attrCollectionMock->expects($this->once())->method('setAttributeSetFilter')->with($attributeSetId)
            ->will($this->returnSelf());

        $attributeData = array(
            'attribute_id' => 1,
            'attribute_code' => 'status',
            'frontend_label' => 'Status',
            'default_value' => '1',
            'is_required' => false,
            'is_user_defined' => false,
            'frontend_input' => 'text'
        );

        // Use magento object for simplicity
        $this->attrCollectionMock->expects($this->once())->method('load')->will($this->returnValue(
            array(new \Magento\Framework\Object($attributeData))
        ));
        $this->attributeBuilderMock->expects($this->once())
            ->method('setId')
            ->with($attributeData['attribute_id'])
            ->will($this->returnSelf());
        $this->attributeBuilderMock->expects($this->once())
            ->method('setCode')
            ->with($attributeData['attribute_code'])
            ->will($this->returnSelf());
        $this->attributeBuilderMock->expects($this->once())
            ->method('setFrontendLabel')
            ->with($attributeData['frontend_label'])
            ->will($this->returnSelf());
        $this->attributeBuilderMock->expects($this->once())
            ->method('setDefaultValue')
            ->with($attributeData['default_value'])
            ->will($this->returnSelf());
        $this->attributeBuilderMock->expects($this->once())
            ->method('setIsRequired')
            ->with($attributeData['is_required'])
            ->will($this->returnSelf());
        $this->attributeBuilderMock->expects($this->once())
            ->method('setIsUserDefined')
            ->with($attributeData['is_user_defined'])
            ->will($this->returnSelf());
        $this->attributeBuilderMock->expects($this->once())
            ->method('setFrontendInput')
            ->with($attributeData['frontend_input'])
            ->will($this->returnSelf());

        $dataObjectMock = $this->getMock(
            '\Magento\Catalog\Service\V1\Data\Eav\Attribute',
            array(),
            array(),
            '',
            false
        );
        $this->attributeBuilderMock->expects($this->once())
            ->method('create')
            ->will($this->returnValue($dataObjectMock));
        $this->assertContains($dataObjectMock, $this->service->getAttributeList($attributeSetId));
    }

    /**
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @expectedExceptionMessage No such entity with attributeSetId = 80085
     */
    public function testGetAttributeListThrowsExceptionIfIdIsNotValid()
    {
        $attributeSetId = 80085;
        $attributeSetMock = $this->getMock(
            '\Magento\Eav\Model\Entity\Attribute\Set',
            array('load', 'getId', 'getName', 'getSortOrder', '__wakeup'),
            array(),
            '',
            false
        );
        $this->setFactoryMock->expects($this->once())->method('create')->will($this->returnValue($attributeSetMock));
        $attributeSetMock->expects($this->once())
            ->method('load')
            ->with($attributeSetId)
            ->will($this->returnSelf());
        $this->attrCollectionMock->expects($this->never())->method('load');
        $this->attributeBuilderMock->expects($this->never())->method('create');
        $this->service->getAttributeList($attributeSetId);
    }
}
