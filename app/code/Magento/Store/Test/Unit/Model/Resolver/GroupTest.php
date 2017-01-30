<?php
/**
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Store\Test\Unit\Model\Resolver;

use \Magento\Store\Model\Resolver\Group;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Test class for \Magento\Store\Model\Resolver\Store
 */
class GroupTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Group
     */
    protected $model;

    /**
     * @var StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeManagerMock;

    protected function setUp()
    {
        $this->storeManagerMock = $this->getMock(
            'Magento\Store\Model\StoreManagerInterface',
            [],
            [],
            '',
            false,
            false
        );

        $this->model = new Group($this->storeManagerMock);
    }

    protected function tearDown()
    {
        unset($this->storeManagerMock);
    }

    public function testGetScope()
    {
        $scopeMock = $this->getMock('Magento\Framework\App\ScopeInterface', [], [], '', false, false);
        $this->storeManagerMock
            ->expects($this->once())
            ->method('getGroup')
            ->with(0)
            ->will($this->returnValue($scopeMock));

        $this->assertEquals($scopeMock, $this->model->getScope());
    }

    /**
     * @expectedException \Magento\Framework\Exception\State\InitException
     */
    public function testGetScopeWithInvalidScope()
    {
        $scopeMock = new \StdClass();
        $this->storeManagerMock
            ->expects($this->once())
            ->method('getGroup')
            ->with(0)
            ->will($this->returnValue($scopeMock));

        $this->assertEquals($scopeMock, $this->model->getScope());
    }
}
