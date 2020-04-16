<?php declare(strict_types=1);
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Config\Test\Unit\Model\Config\Structure\Element\Group;

use Magento\Config\Model\Config\Structure\Element\Group;
use Magento\Config\Model\Config\Structure\Element\Group\Proxy;
use Magento\Framework\ObjectManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ProxyTest extends TestCase
{
    /**
     * @var Proxy
     */
    protected $_model;

    /**
     * @var MockObject
     */
    protected $_objectManagerMock;

    protected function setUp(): void
    {
        $this->_objectManagerMock = $this->createMock(ObjectManagerInterface::class);
        $this->_model = new Proxy($this->_objectManagerMock);
    }

    protected function tearDown(): void
    {
        unset($this->_model);
        unset($this->_objectManagerMock);
    }

    public function testProxyInitializesProxiedObjectOnFirstCall()
    {
        $groupMock = $this->createMock(Group::class);

        $groupMock->expects($this->once())->method('setData');
        $groupMock->expects($this->once())->method('getId')->will($this->returnValue('group_id'));
        $this->_objectManagerMock->expects(
            $this->once()
        )->method(
            'create'
        )->with(
            Group::class
        )->will(
            $this->returnValue($groupMock)
        );

        $this->_model->setData([], '');
        $this->assertEquals('group_id', $this->_model->getId());
    }
}
