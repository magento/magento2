<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Test\Unit\Model\Group;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Class DataProviderTest
 *
 * Test for class \Magento\Customer\Model\Group\DataProvider
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.LongVariable)
 */
class DataProviderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Customer\Model\ResourceModel\Group\CollectionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $collectionFactoryMock;

    /**
     * @var \Magento\Framework\Registry|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $registryMock;

    /**
     * @var \Magento\Customer\Api\GroupRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $groupRepositoryMock;

    /**
     * @var \Magento\Tax\Helper\Data|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $taxHelperMock;

    /**
     * @var \Magento\Customer\Model\ResourceModel\Group\Collection|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $collectionMock;

    /**
     * {@inheritDoc}
     */
    protected function setUp()
    {
        $this->collectionFactoryMock = $this->createPartialMock(
            \Magento\Customer\Model\ResourceModel\Group\CollectionFactory::class,
            ['create']
        );

        $this->collectionMock = $this->createMock(\Magento\Customer\Model\ResourceModel\Group\Collection::class);
        $this->collectionFactoryMock->expects($this->once())->method('create')->willReturn($this->collectionMock);
        $this->registryMock = $this->createMock(\Magento\Framework\Registry::class);
        $this->groupRepositoryMock = $this->getMockBuilder(\Magento\Customer\Api\GroupRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->taxHelperMock = $this->getMockBuilder(\Magento\Tax\Helper\Data::class)
            ->setMethods(['getDefaultCustomerTaxClass'])
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @return void
     * @covers \Magento\Customer\Model\Group\DataProvider::getData
     */
    public function testGetData()
    {
        $groupId = 22;
        $groupData = ['customer_group_code' => 'Customer Group Code', 'tax_class_id' => 1];

        $groupMock = $this->createPartialMock(\Magento\Customer\Model\Group::class, [
            'load',
            'getId',
            'getData'
        ]);
        $this->collectionMock->expects($this->once())->method('getItems')->willReturn([$groupMock]);

        $groupMock->expects($this->atLeastOnce())->method('getId')->willReturn($groupId);
        $groupMock->expects($this->once())->method('getData')->willReturn($groupData);

        /** @var \Magento\Customer\Model\Group\DataProvider $dataProvider */
        $dataProvider = (new ObjectManager($this))->getObject(
            \Magento\Customer\Model\Group\DataProvider::class,
            [
                'name' => 'test-name',
                'primaryFieldName' => 'primary-field-name',
                'requestFieldName' => 'request-field-name',
                'groupCollectionFactory' => $this->collectionFactoryMock,
                'registry' => $this->registryMock,
                'groupRepository' => $this->groupRepositoryMock,
                'taxHelper' => $this->taxHelperMock,
            ]
        );

        $this->assertEquals([$groupId => $groupData], $dataProvider->getData());
        // Load from object-cache the second time
        $this->assertEquals([$groupId => $groupData], $dataProvider->getData());
    }

    /**
     * @return void
     * @covers \Magento\Customer\Model\Group\DataProvider::getMeta
     */
    public function testGetMetaForNotLoggedInGroup()
    {
        /** @var \Magento\Customer\Model\Group\DataProvider $dataProvider */
        $dataProvider = (new ObjectManager($this))->getObject(
            \Magento\Customer\Model\Group\DataProvider::class,
            [
                'name' => 'test-name',
                'primaryFieldName' => 'primary-field-name',
                'requestFieldName' => 'request-field-name',
                'groupCollectionFactory' => $this->collectionFactoryMock,
                'registry' => $this->registryMock,
                'groupRepository' => $this->groupRepositoryMock,
                'taxHelper' => $this->taxHelperMock,
            ]
        );

        $this->registryMock->expects($this->any())
            ->method('registry')
            ->willReturn('0');

        $customerGroupMock = $this->getMockBuilder(\Magento\Customer\Api\Data\GroupInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $customerGroupMock->expects($this->any())
            ->method('getId')
            ->willReturn(\Magento\Customer\Model\GroupManagement::NOT_LOGGED_IN_ID);
        $customerGroupMock->expects($this->any())->method('getCode')->willReturn('NOT LOGGED IN');

        $this->groupRepositoryMock->expects($this->any())->method('getById')->willReturn($customerGroupMock);

        $meta = $dataProvider->getMeta();
        $this->assertNotEmpty($meta);
        $this->assertTrue($meta['general']['children']['customer_group_code']['arguments']['data']['config']['disabled']);
    }
}
