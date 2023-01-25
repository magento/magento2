<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Test\Unit\Model;

use Magento\Customer\Model\CustomerManagement;
use Magento\Customer\Model\ResourceModel\Customer\Collection;
use Magento\Customer\Model\ResourceModel\Customer\CollectionFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CustomerManagementTest extends TestCase
{
    /**
     * @var CustomerManagement
     */
    protected $model;

    /**
     * @var CollectionFactory|MockObject
     */
    protected $customersFactoryMock;

    protected function setUp(): void
    {
        $this->customersFactoryMock = $this->createPartialMock(
            CollectionFactory::class,
            ['create']
        );
        $this->model = new CustomerManagement(
            $this->customersFactoryMock
        );
    }

    public function testGetCount()
    {
        $customersMock = $this->createMock(Collection::class);

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
