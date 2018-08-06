<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Test\Unit\Fixtures;

use Magento\Customer\Api\Data\GroupInterface;
use Magento\Customer\Api\Data\GroupInterfaceFactory;
use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Customer\Model\ResourceModel\Group\CollectionFactory;
use Magento\Setup\Fixtures\CustomerGroupsFixture;

/**
 * Test Customer Groups generation
 */
class CustomerGroupsFixtureTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Setup\Fixtures\FixtureModel
     */
    private $fixtureModelMock;

    /**
     * @var CollectionFactory
     */
    private $groupCollectionFactoryMock;

    /**
     * @var GroupRepositoryInterface
     */
    private $groupRepositoryMock;

    /**
     * @var GroupInterfaceFactory
     */
    private $groupFactoryMock;

    /**
     * @var GroupInterface
     */
    private $groupDataObjectMock;

    /**
     * @var \Magento\Setup\Fixtures\IndexersStatesApplyFixture
     */
    private $model;

    public function testExecute()
    {
        $this->fixtureModelMock = $this->getMockBuilder(\Magento\Setup\Fixtures\FixtureModel::class)
            ->disableOriginalConstructor()
            ->getMock();

        //Mock repository for customer groups
        $this->groupRepositoryMock = $this->getMockBuilder(GroupRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        //Mock for customer groups collection
        $this->groupCollectionFactoryMock = $this->getMockBuilder(CollectionFactory::class)
            ->setMethods(['create', 'getSize'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->groupCollectionFactoryMock
            ->expects($this->once())
            ->method('create')
            ->willReturn($this->groupCollectionFactoryMock);

        $this->groupCollectionFactoryMock
            ->expects($this->once())
            ->method('getSize')
            ->willReturn(0);

        //Mock customer groups data object
        $this->groupDataObjectMock = $this->getMockBuilder(GroupInterface::class)
            ->setMethods(['setCode', 'setTaxClassId', 'save'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        //Mock customer groups factory
        $this->groupFactoryMock = $this->getMockBuilder(GroupInterfaceFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->groupFactoryMock
            ->expects($this->once())
            ->method('create')
            ->willReturn($this->groupDataObjectMock);

        $this->groupDataObjectMock
            ->expects($this->once())
            ->method('setCode')
            ->willReturn($this->groupDataObjectMock);

        $this->groupDataObjectMock
            ->expects($this->once())
            ->method('setTaxClassId')
            ->willReturn($this->groupDataObjectMock);

        $this->groupRepositoryMock
            ->expects($this->once())
            ->method('save')
            ->willReturn($this->groupDataObjectMock);

        $this->fixtureModelMock
            ->expects($this->once())
            ->method('getValue')
            ->will($this->returnValue(1));

        $this->model = new CustomerGroupsFixture(
            $this->fixtureModelMock,
            $this->groupCollectionFactoryMock,
            $this->groupRepositoryMock,
            $this->groupFactoryMock
        );

        $this->model->execute();
    }
}
