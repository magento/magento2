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
namespace Magento\Eav\Model;

class AttributeSetManagementTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var AttributeSetManagement
     */
    private $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $repositoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $eavConfigMock;

    protected function setUp()
    {
        $this->repositoryMock = $this->getMock('Magento\Eav\Api\AttributeSetRepositoryInterface');
        $this->eavConfigMock = $this->getMock('Magento\Eav\Model\Config', array(), array(), '', false);

        $this->model = new AttributeSetManagement(
            $this->eavConfigMock,
            $this->repositoryMock
        );
    }

    public function testCreate()
    {
        $skeletonId = 1;
        $entityTypeCode = 'catalog_product';
        $entityTypeId = 4;
        $entityTypeMock = $this->getMock('Magento\Eav\Model\Entity\Type', array(), array(), '', false);
        $entityTypeMock->expects($this->any())->method('getId')->will($this->returnValue($entityTypeId));
        $this->eavConfigMock->expects($this->once())
            ->method('getEntityType')
            ->with($entityTypeCode)
            ->will($this->returnValue($entityTypeMock));
        $attributeSetMock = $this->getMock(
            'Magento\Eav\Model\Entity\Attribute\Set',
            array('validate', 'getId', 'setEntityTypeId', 'initFromSkeleton'),
            array(),
            '',
            false
        );
        $attributeSetMock->expects($this->once())->method('validate');
        $attributeSetMock->expects($this->once())->method('setEntityTypeId')->with($entityTypeId);
        $this->repositoryMock->expects($this->exactly(2))
            ->method('save')
            ->with($attributeSetMock)
            ->will($this->returnValue($attributeSetMock));
        $attributeSetMock->expects($this->once())->method('initFromSkeleton')->with($skeletonId);
        $this->assertEquals($attributeSetMock, $this->model->create($entityTypeCode, $attributeSetMock, $skeletonId));
    }

    /**
     * @expectedException \Magento\Framework\Exception\InputException
     * @expectedExceptionMessage Invalid value of "1" provided for the id field.
     */
    public function testCreateThrowsExceptionIfGivenAttributeSetAlreadyHasId()
    {
        $skeletonId = 1;
        $entityTypeCode = 'catalog_product';
        $attributeSetMock = $this->getMock(
            'Magento\Eav\Model\Entity\Attribute\Set',
            array('validate', 'getId', 'setEntityTypeId', 'initFromSkeleton'),
            array(),
            '',
            false
        );
        $attributeSetMock->expects($this->any())->method('getId')->will($this->returnValue(1));
        $this->repositoryMock->expects($this->never())->method('save')->with($attributeSetMock);
        $attributeSetMock->expects($this->never())->method('initFromSkeleton')->with($skeletonId);
        $this->model->create($entityTypeCode, $attributeSetMock, $skeletonId);
    }

    /**
     * @expectedException \Magento\Framework\Exception\InputException
     * @expectedExceptionMessage Invalid value of "0" provided for the skeletonId field.
     */
    public function testCreateThrowsExceptionIfGivenSkeletonIdIsInvalid()
    {
        $skeletonId = 0;
        $entityTypeCode = 'catalog_product';
        $attributeSetMock = $this->getMock(
            'Magento\Eav\Model\Entity\Attribute\Set',
            array('validate', 'getId', 'setEntityTypeId', 'initFromSkeleton'),
            array(),
            '',
            false
        );
        $this->repositoryMock->expects($this->never())->method('save')->with($attributeSetMock);
        $attributeSetMock->expects($this->never())->method('initFromSkeleton')->with($skeletonId);
        $this->model->create($entityTypeCode, $attributeSetMock, $skeletonId);
    }
}
