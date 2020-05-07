<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Store\Test\Unit\Model\Resolver;

use Magento\Framework\App\ScopeInterface;
use Magento\Framework\Exception\State\InitException;
use Magento\Store\Model\Resolver\Group;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test class for \Magento\Store\Model\Resolver\Store
 */
class GroupTest extends TestCase
{
    /**
     * @var Group
     */
    protected $model;

    /**
     * @var StoreManagerInterface|MockObject
     */
    protected $storeManagerMock;

    protected function setUp(): void
    {
        $this->storeManagerMock = $this->getMockForAbstractClass(StoreManagerInterface::class);

        $this->model = new Group($this->storeManagerMock);
    }

    protected function tearDown(): void
    {
        unset($this->storeManagerMock);
    }

    public function testGetScope()
    {
        $scopeMock = $this->getMockForAbstractClass(ScopeInterface::class);
        $this->storeManagerMock
            ->expects($this->once())
            ->method('getGroup')
            ->with(0)
            ->willReturn($scopeMock);

        $this->assertEquals($scopeMock, $this->model->getScope());
    }

    public function testGetScopeWithInvalidScope()
    {
        $this->expectException(InitException::class);
        $scopeMock = new \StdClass();
        $this->storeManagerMock
            ->expects($this->once())
            ->method('getGroup')
            ->with(0)
            ->willReturn($scopeMock);

        $this->assertEquals($scopeMock, $this->model->getScope());
    }
}
