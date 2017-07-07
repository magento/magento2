<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Test\Unit\Model;

class CustomerManagementTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Customer\Model\CustomerManagement
     */
    protected $model;

    /**
     * @var \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $customersFactoryMock;

    protected function setUp()
    {
        $this->customersFactoryMock = $this->getMock(
            \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory::class,
            ['create'],
            [],
            '',
            false
        );
        $this->model = new \Magento\Customer\Model\CustomerManagement(
            $this->customersFactoryMock
        );
    }

    public function testGetCount()
    {
        $customersMock = $this->getMock(
            \Magento\Customer\Model\ResourceModel\Customer\Collection::class,
            [],
            [],
            '',
            false
        );

        $this->customersFactoryMock
            ->expects($this->once())
            ->method('create')
            ->willReturn($customersMock);
        $customersMock
            ->expects($this->once())
            ->method('getSize')
            ->willReturn('expected');

        $this->assertEquals(
            'expected',
            $this->model->getCount()
        );
    }
}
