<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Store\Test\Unit\Model;

use Magento\Store\Model\ResourceModel\Store\Collection;
use Magento\Store\Model\ResourceModel\Store\CollectionFactory;
use Magento\Store\Model\StoreManagement;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class StoreManagementTest extends TestCase
{
    /**
     * @var StoreManagement
     */
    protected $model;

    /**
     * @var CollectionFactory|MockObject
     */
    protected $storesFactoryMock;

    protected function setUp(): void
    {
        $this->storesFactoryMock = $this->createPartialMock(
            CollectionFactory::class,
            ['create']
        );
        $this->model = new StoreManagement(
            $this->storesFactoryMock
        );
    }

    public function testGetCount()
    {
        $storesMock = $this->createMock(Collection::class);

        $this->storesFactoryMock
            ->expects($this->once())
            ->method('create')
            ->willReturn($storesMock);
        $storesMock
            ->expects($this->once())
            ->method('setWithoutDefaultFilter')
            ->willReturnSelf();
        $storesMock
            ->expects($this->once())
            ->method('getSize')
            ->willReturn('expected');

        $this->assertEquals(
            'expected',
            $this->model->getCount()
        );
    }
}
