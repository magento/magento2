<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Controller\Adminhtml\Product\Initialization\Helper;

use Magento\Catalog\Controller\Adminhtml\Product\Initialization\Helper\HandlerFactory;
use Magento\Catalog\Controller\Adminhtml\Product\Initialization\Helper\HandlerInterface;
use Magento\Catalog\Controller\Adminhtml\Product\Initialization\Helper\Plugin\Handler\Composite;
use Magento\Framework\DataObject;
use Magento\Framework\ObjectManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class HandlerFactoryTest extends TestCase
{
    /**
     * @var HandlerFactory
     */
    protected $_model;

    /**
     * @var MockObject
     */
    protected $_objectManagerMock;

    protected function setUp(): void
    {
        $this->_objectManagerMock = $this->getMockForAbstractClass(ObjectManagerInterface::class);
        $this->_model = new HandlerFactory($this->_objectManagerMock);
    }

    public function testCreateWithInvalidType()
    {
        $this->expectException('\InvalidArgumentException');
        $this->expectExceptionMessage(DataObject::class . ' does not implement ' .
            HandlerInterface::class);
        $this->_objectManagerMock->expects($this->never())->method('create');
        $this->_model->create(DataObject::class);
    }

    public function testCreateWithValidType()
    {
        $this->_objectManagerMock->expects(
            $this->once()
        )->method(
            'create'
        )->with(
            Composite::class
        )->willReturn(
            'object'
        );
        $this->assertEquals(
            'object',
            $this->_model->create(
                Composite::class
            )
        );
    }
}
