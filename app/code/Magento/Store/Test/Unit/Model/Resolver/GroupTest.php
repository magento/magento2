<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Store\Test\Unit\Model\Resolver;

use \Magento\Store\Model\Resolver\Group;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Test class for \Magento\Store\Model\Resolver\Store
 */
class GroupTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Group
     */
    protected $model;

    /**
     * @var StoreManagerInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $storeManagerMock;

    protected function setUp(): void
    {
        $this->storeManagerMock = $this->createMock(\Magento\Store\Model\StoreManagerInterface::class);

        $this->model = new Group($this->storeManagerMock);
    }

    protected function tearDown(): void
    {
        unset($this->storeManagerMock);
    }

    public function testGetScope()
    {
        $scopeMock = $this->createMock(\Magento\Framework\App\ScopeInterface::class);
        $this->storeManagerMock
            ->expects($this->once())
            ->method('getGroup')
            ->with(0)
            ->willReturn($scopeMock);

        $this->assertEquals($scopeMock, $this->model->getScope());
    }

    /**
     */
    public function testGetScopeWithInvalidScope()
    {
        $this->expectException(\Magento\Framework\Exception\State\InitException::class);

        $scopeMock = new \StdClass();
        $this->storeManagerMock
            ->expects($this->once())
            ->method('getGroup')
            ->with(0)
            ->willReturn($scopeMock);

        $this->assertEquals($scopeMock, $this->model->getScope());
    }
}
