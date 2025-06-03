<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Setup\Test\Unit\Fixtures;

use Magento\Customer\Api\Data\GroupInterface;
use Magento\Customer\Api\Data\GroupInterfaceFactory;
use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Customer\Model\ResourceModel\Group\CollectionFactory;
use Magento\Setup\Fixtures\CustomerGroupsFixture;
use Magento\Setup\Fixtures\FixtureModel;
use Magento\Setup\Fixtures\IndexersStatesApplyFixture;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test Customer Groups generation
 */
class CustomerGroupsFixtureTest extends TestCase
{
    /**
     * @var MockObject|FixtureModel
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
     * @var IndexersStatesApplyFixture
     */
    private $model;

    public function testExecute()
    {
        $this->fixtureModelMock = $this->getMockBuilder(FixtureModel::class)
            ->disableOriginalConstructor()
            ->getMock();

        //Mock repository for customer groups
        $this->groupRepositoryMock = $this->getMockBuilder(GroupRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        //Mock for customer groups collection
        $this->groupCollectionFactoryMock = $this->getMockBuilder(CollectionFactory::class)
            ->addMethods(['getSize'])
            ->onlyMethods(['create'])
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
            ->addMethods(['save'])
            ->onlyMethods(['setCode', 'setTaxClassId'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        //Mock customer groups factory
        $this->groupFactoryMock = $this->getMockBuilder(GroupInterfaceFactory::class)
            ->onlyMethods(['create'])
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
            ->willReturn(1);

        $this->model = new CustomerGroupsFixture(
            $this->fixtureModelMock,
            $this->groupCollectionFactoryMock,
            $this->groupRepositoryMock,
            $this->groupFactoryMock
        );

        $this->model->execute();
    }
}
