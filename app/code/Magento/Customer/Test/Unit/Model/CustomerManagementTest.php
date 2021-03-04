<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Test\Unit\Model;

class CustomerManagementTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Customer\Model\CustomerManagement
     */
    protected $model;

    /**
     * @var \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $customersFactoryMock;

    protected function setUp(): void
    {
        $this->customersFactoryMock = $this->createPartialMock(
            \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory::class,
            ['create']
        );
        $this->model = new \Magento\Customer\Model\CustomerManagement(
            $this->customersFactoryMock
        );
    }

    public function testGetCount()
    {
        $customersMock = $this->createMock(\Magento\Customer\Model\ResourceModel\Customer\Collection::class);

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
