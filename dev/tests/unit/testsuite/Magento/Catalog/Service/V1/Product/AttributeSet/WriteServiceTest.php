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

use Magento\Catalog\Service\V1\Data\Eav\AttributeSet;

class WriteServiceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $setFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $eavConfigMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $entityTypeMock;

    /**
     * @var \Magento\Catalog\Service\V1\Product\AttributeSet\WriteService
     */
    protected $service;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $setMock;

    /**
     * @var int default attribute set id
     */
    protected $defaultSetId = 4;

    protected function setUp()
    {
        $this->setFactoryMock = $this->getMock('\Magento\Eav\Model\Entity\Attribute\SetFactory',
            array('create'), array(), '', false);
        $this->eavConfigMock = $this->getMock('\Magento\Eav\Model\Config', array(), array(), '', false);
        $this->entityTypeMock = $this->getMock('\Magento\Eav\Model\Entity\Type', array(), array(), '', false);
        $this->eavConfigMock->expects($this->any())->method('getEntityType')
            ->with(\Magento\Catalog\Model\Product::ENTITY)
            ->will($this->returnValue($this->entityTypeMock));
        $this->setMock = $this->getMock(
            '\Magento\Eav\Model\Entity\Attribute\Set',
            array(
                'setData', 'getData', 'validate', 'save', 'getId', 'delete', 'setAttributeSetName', 'setSortOrder',
                'load', 'initFromSkeleton', '__wakeup', 'getEntityTypeId',
            ),
            array(),
            '',
            false
        );
        $this->service = new WriteService(
            $this->setFactoryMock,
            $this->eavConfigMock
        );
    }

    /**
     * @expectedException \Magento\Framework\Exception\InputException
     */
    public function testCreateWithExistingId()
    {
        $setDataMock = $this->getMock(
            '\Magento\Catalog\Service\V1\Data\Eav\AttributeSet',
            array(),
            array(),
            '',
            false
        );
        $this->setFactoryMock->expects($this->any())->method('create')->will($this->returnValue($this->setMock));
        $setDataMock->expects($this->any())->method('getId')->will($this->returnValue($this->defaultSetId));

        $this->service->create($setDataMock, $this->defaultSetId);
    }

    /**
     * @expectedException \Magento\Framework\Exception\InputException
     */
    public function testCreateWithEmptyName()
    {
        $setDataMock = $this->getMock(
            '\Magento\Catalog\Service\V1\Data\Eav\AttributeSet',
            array(),
            array(),
            '',
            false
        );
        $setDataMock->expects($this->any())->method('getId')->will($this->returnValue(null));
        $setDataMock->expects($this->any())->method('getName')->will($this->returnValue(null));
        $setDataMock->expects($this->any())->method('getSortOrder')->will($this->returnValue(20));

        $this->setFactoryMock->expects($this->once())->method('create')->will($this->returnValue($this->setMock));
        $exception = new \Magento\Eav\Exception('empty name');
        $this->setMock->expects($this->once())->method('validate')->will($this->throwException($exception));
        $this->setMock->expects($this->never())->method('save');

        $this->service->create($setDataMock, $this->defaultSetId);
    }

    /**
     * @expectedException \Magento\Framework\Exception\InputException
     */
    public function testCreateWithNoSkeletonId()
    {
        $setDataMock = $this->getMock(
            '\Magento\Catalog\Service\V1\Data\Eav\AttributeSet',
            array(),
            array(),
            '',
            false
        );
        $setDataMock->expects($this->any())->method('getId')->will($this->returnValue(null));
        $setDataMock->expects($this->any())->method('getName')->will($this->returnValue('cool attribute set'));
        $setDataMock->expects($this->any())->method('getSortOrder')->will($this->returnValue(20));

        $this->setFactoryMock->expects($this->once())->method('create')->will($this->returnValue($this->setMock));
        $this->setMock->expects($this->once())->method('validate')->will($this->returnValue(true));

        $this->service->create($setDataMock, null);
    }

    /**
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     */
    public function testCreateWithAbsentSkeleton()
    {
        $absentId = 134523;
        $setDataMock = $this->getMock(
            '\Magento\Catalog\Service\V1\Data\Eav\AttributeSet',
            array(),
            array(),
            '',
            false
        );
        $setDataMock->expects($this->any())->method('getId')->will($this->returnValue(null));
        $setDataMock->expects($this->any())->method('getName')->will($this->returnValue('cool attribute set'));
        $setDataMock->expects($this->any())->method('getSortOrder')->will($this->returnValue(20));

        $this->setFactoryMock->expects($this->exactly(2))->method('create')->will($this->returnValue($this->setMock));
        $this->setMock->expects($this->once())->method('validate')->will($this->returnValue(true));
        $skeletonSetMock = $this->getMock(
            '\Magento\Eav\Model\Entity\Attribute\Set',
            array('setData', 'validate', 'save', 'getId', 'getData', 'load', '__wakeup'),
            array(),
            '',
            false
        );
        $skeletonSetMock->expects($this->once())->method('getData')->will($this->returnValue(array()));
        $this->setMock->expects($this->once())->method('load')
            ->with($absentId)
            ->will($this->returnValue($skeletonSetMock));

        $this->service->create($setDataMock, $absentId);
    }

    public function testCreatePositive()
    {
        $setId = 123321;
        $setDataMock = $this->getMock(
            '\Magento\Catalog\Service\V1\Data\Eav\AttributeSet',
            array(),
            array(),
            '',
            false
        );
        $setDataMock->expects($this->any())->method('getId')->will($this->returnValue(null));
        $setDataMock->expects($this->any())->method('getName')->will($this->returnValue('cool attribute set'));
        $setDataMock->expects($this->any())->method('getSortOrder')->will($this->returnValue(20));

        $this->setFactoryMock->expects($this->exactly(2))->method('create')->will($this->returnValue($this->setMock));
        $this->setMock->expects($this->once())->method('validate')->will($this->returnValue(true));
        $this->setMock->expects($this->exactly(2))->method('save');
        $this->setMock->expects($this->once())->method('getId')->will($this->returnValue($setId));
        $skeletonSetMock = $this->getMock(
            '\Magento\Eav\Model\Entity\Attribute\Set',
            array('setData', 'validate', 'save', 'getId', 'load', 'getData', '__wakeup'),
            array(),
            '',
            false
        );
        $this->setMock->expects($this->once())->method('load')
            ->with($this->defaultSetId)
            ->will($this->returnValue($skeletonSetMock));
        $skeletonSetMock->expects($this->once())->method('getData')->will($this->returnValue(array(1, 2, 3)));
        $this->setMock->expects($this->once())->method('initFromSkeleton')->with($this->defaultSetId);

        $this->assertEquals($setId, $this->service->create($setDataMock, $this->defaultSetId));
    }

    /**
     * @expectedException \Magento\Framework\Exception\InputException
     */
    public function testRemoveInvalidId()
    {
        $this->setFactoryMock->expects($this->never())->method('create');
        $this->service->remove('absent id');
    }

    /**
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     */
    public function testRemoveAbsentSet()
    {
        $id = 145678;
        $this->setFactoryMock->expects($this->once())->method('create')->will($this->returnValue($this->setMock));
        $this->setMock->expects($this->once())->method('load')->with($id)->will($this->returnSelf());
        $this->setMock->expects($this->once())->method('getData')->will($this->returnValue(null));
        $this->setMock->expects($this->never())->method('delete');
        $this->service->remove($id);
    }

    /**
     * @expectedException \Magento\Framework\Exception\InputException
     */
    public function testRemoveWrongEntity()
    {
        $id = 14;

        $this->setFactoryMock->expects($this->once())->method('create')->will($this->returnValue($this->setMock));
        $typeMock = $this->getMock('\Magento\Eav\Model\Entity\Type', array('getId', '__wakeup'), array(), '', false);
        $this->eavConfigMock->expects($this->any())->method('getEntityType')->will($this->returnValue($typeMock));
        $typeMock->expects($this->any())->method('getId')->will($this->returnValue(4));

        $this->setMock->expects($this->once())->method('load')->with($id)->will($this->returnSelf());
        $this->setMock->expects($this->once())->method('getData')->will($this->returnValue(array(5, 7, 9)));
        $this->setMock->expects($this->any())->method('getEntityTypeId')->will($this->returnValue(1));
        $this->setMock->expects($this->never())->method('delete');
        $this->service->remove($id);
    }

    public function testRemovePositive()
    {
        $id = 456;
        $this->setFactoryMock->expects($this->once())->method('create')->will($this->returnValue($this->setMock));
        $this->setMock->expects($this->once())->method('load')->with($id)->will($this->returnSelf());
        $this->setMock->expects($this->once())->method('getData')->will($this->returnValue(array(5, 6, 7)));
        $this->setMock->expects($this->once())->method('delete');

        $this->service->remove($id);
    }

    public function testUpdate()
    {
        $data = array(
            AttributeSet::ID => 4,
            AttributeSet::NAME => 'Test Attribute Set',
            AttributeSet::ORDER => 200,
        );
        $attributeSetDataMock = $this->getMock('Magento\Catalog\Service\V1\Data\Eav\AttributeSet',
            array(), array(), '', false);
        $attributeSetDataMock->expects($this->any())->method('getId')
            ->will($this->returnValue($data[AttributeSet::ID]));
        $attributeSetDataMock->expects($this->any())->method('getName')
            ->will($this->returnValue($data[AttributeSet::NAME]));
        $attributeSetDataMock->expects($this->any())->method('getSortOrder')
            ->will($this->returnValue($data[AttributeSet::ORDER]));

        $entityTypeId = 4;
        $this->entityTypeMock->expects($this->once())->method('getId')->will($this->returnValue($entityTypeId));

        $this->setFactoryMock->expects($this->once())->method('create')
            ->will($this->returnValue($this->setMock));
        $this->setMock->expects($this->once())
            ->method('load')
            ->with($data[AttributeSet::ID])
            ->will($this->returnSelf());
        $this->setMock->expects($this->any())
            ->method('getEntityTypeId')
            ->will($this->returnValue($entityTypeId));
        $this->setMock->expects($this->any())->method('getId')
            ->will($this->returnValue($data[AttributeSet::ID]));
        $this->setMock->expects($this->once())->method('setAttributeSetName')->with($data[AttributeSet::NAME])
            ->will($this->returnSelf());
        $this->setMock->expects($this->once())->method('setSortOrder')->with($data[AttributeSet::ORDER])
            ->will($this->returnSelf());
        $this->setMock->expects($this->once())->method('save')
            ->will($this->returnSelf());
        $this->assertEquals($data[AttributeSet::ID], $this->service->update($attributeSetDataMock));
    }

    /**
     * @expectedException \Magento\Framework\Exception\InputException
     * @expectedExceptionMessage id is a required field.
     */
    public function testUpdateThrowsExceptionIfAttributeSetIdIsNotSpecified()
    {
        $data = array(
            AttributeSet::ID => null,
            AttributeSet::NAME => 'Test Attribute Set',
            AttributeSet::ORDER => 200,
        );
        $attributeSetDataMock = $this->getMock('Magento\Catalog\Service\V1\Data\Eav\AttributeSet',
            array(), array(), '', false);
        $attributeSetDataMock->expects($this->any())->method('getId')
            ->will($this->returnValue($data[AttributeSet::ID]));
        $this->service->update($attributeSetDataMock);
    }

    /**
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @expectedExceptionMessage No such entity with id = 9999
     */
    public function testUpdateThrowsExceptionIfAttributeSetIdIsInvalid()
    {
        $entityTypeId = 4;
        $data = array(
            AttributeSet::ID => 9999,
            AttributeSet::NAME => 'Test Attribute Set',
            AttributeSet::ORDER => 200,
        );
        $attributeSetDataMock = $this->getMock('Magento\Catalog\Service\V1\Data\Eav\AttributeSet',
            array(), array(), '', false);
        $attributeSetDataMock->expects($this->any())->method('getId')
            ->will($this->returnValue($data[AttributeSet::ID]));

        $this->entityTypeMock->expects($this->once())->method('getId')->will($this->returnValue($entityTypeId));

        $this->setFactoryMock->expects($this->once())->method('create')
            ->will($this->returnValue($this->setMock));
        $this->setMock->expects($this->once())
            ->method('load')
            ->with($data[AttributeSet::ID])
            ->will($this->returnSelf());
        $this->setMock->expects($this->any())
            ->method('getEntityTypeId')
            ->will($this->returnValue($entityTypeId));
        $this->setMock->expects($this->any())->method('getId');
        $this->setMock->expects($this->never())->method('setAttributeSetName');
        $this->setMock->expects($this->never())->method('setSortOrder');
        $this->setMock->expects($this->never())->method('save');
        $this->service->update($attributeSetDataMock);
    }

    /**
     * @expectedException \Magento\Framework\Exception\StateException
     * @expectedExceptionMessage Default attribute set can not be deleted
     */
    public function testRemoveDefaultAttributeSet()
    {
        $id = 456;
        $this->setFactoryMock->expects($this->once())->method('create')->will($this->returnValue($this->setMock));
        $this->setMock->expects($this->once())->method('load')->with($id)->will($this->returnSelf());
        $this->setMock->expects($this->once())->method('getData')->will($this->returnValue(array(5, 6, 7)));
        $this->setMock->expects($this->never())->method('delete');
        $this->entityTypeMock
            ->expects($this->once())
            ->method('getDefaultAttributeSetId')
            ->will($this->returnValue($id));
        $this->service->remove($id);
    }

}
