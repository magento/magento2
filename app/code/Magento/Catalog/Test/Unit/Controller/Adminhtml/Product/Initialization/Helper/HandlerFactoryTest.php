<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Controller\Adminhtml\Product\Initialization\Helper;

use \Magento\Catalog\Controller\Adminhtml\Product\Initialization\Helper\HandlerFactory;

class HandlerFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var HandlerFactory
     */
    protected $_model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_objectManagerMock;

    protected function setUp()
    {
        $this->_objectManagerMock = $this->getMock(\Magento\Framework\ObjectManagerInterface::class);
        $this->_model = new HandlerFactory($this->_objectManagerMock);
    }

    public function testCreateWithInvalidType()
    {
        $this->setExpectedException(
            '\InvalidArgumentException',
            \Magento\Framework\DataObject::class . ' does not implement ' .
            \Magento\Catalog\Controller\Adminhtml\Product\Initialization\Helper\HandlerInterface::class
        );
        $this->_objectManagerMock->expects($this->never())->method('create');
        $this->_model->create(\Magento\Framework\DataObject::class);
    }

    public function testCreateWithValidType()
    {
        $this->_objectManagerMock->expects(
            $this->once()
        )->method(
            'create'
        )->with(
            \Magento\Catalog\Controller\Adminhtml\Product\Initialization\Helper\Plugin\Handler\Composite::class
        )->will(
            $this->returnValue('object')
        );
        $this->assertEquals(
            'object',
            $this->_model->create(
                \Magento\Catalog\Controller\Adminhtml\Product\Initialization\Helper\Plugin\Handler\Composite::class
            )
        );
    }
}
