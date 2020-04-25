<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Controller\Test\Unit\Controller;

use Magento\Framework\App\Request\Http;
use Magento\Framework\App\ViewInterface;
use Magento\Framework\Controller\Noroute\Index;
use Magento\Framework\DataObject;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class NorouteTest extends TestCase
{
    /**
     * @var \Magento\Framework\Controller\Noroute
     */
    protected $_controller;

    /**
     * @var MockObject
     */
    protected $_requestMock;

    /**
     * @var MockObject
     */
    protected $_viewMock;

    /**
     * @var MockObject
     */
    protected $_statusMock;

    protected function setUp(): void
    {
        $helper = new ObjectManager($this);
        $this->_requestMock = $this->createMock(Http::class);
        $this->_viewMock = $this->createMock(ViewInterface::class);
        $this->_statusMock =
            $this->createPartialMock(DataObject::class, ['getLoaded', 'getForwarded']);
        $this->_controller = $helper->getObject(
            Index::class,
            ['request' => $this->_requestMock, 'view' => $this->_viewMock]
        );
    }

    public function testIndexActionWhenStatusNotLoaded()
    {
        $this->_requestMock->expects(
            $this->once()
        )->method(
            'getParam'
        )->with(
            '__status__'
        )->will(
            $this->returnValue($this->_statusMock)
        );
        $this->_statusMock->expects($this->any())->method('getLoaded')->will($this->returnValue(false));
        $this->_viewMock->expects($this->once())->method('loadLayout')->with(['default', 'noroute']);
        $this->_viewMock->expects($this->once())->method('renderLayout');
        $this->_controller->execute();
    }

    public function testIndexActionWhenStatusLoaded()
    {
        $this->_requestMock->expects(
            $this->once()
        )->method(
            'getParam'
        )->with(
            '__status__'
        )->will(
            $this->returnValue($this->_statusMock)
        );
        $this->_statusMock->expects($this->any())->method('getLoaded')->will($this->returnValue(true));
        $this->_statusMock->expects($this->any())->method('getForwarded')->will($this->returnValue(false));
        $this->_viewMock->expects($this->never())->method('loadLayout');
        $this->_requestMock->expects(
            $this->once()
        )->method(
            'setActionName'
        )->will(
            $this->returnValue($this->_requestMock)
        );
        $this->_controller->execute();
    }

    public function testIndexActionWhenStatusNotInstanceofMagentoObject()
    {
        $this->_requestMock->expects(
            $this->once()
        )->method(
            'getParam'
        )->with(
            '__status__'
        )->will(
            $this->returnValue('string')
        );
        $this->_controller->execute();
    }
}
