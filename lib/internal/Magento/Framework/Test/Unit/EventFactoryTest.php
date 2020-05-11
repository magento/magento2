<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Test\Unit;

use Magento\Framework\Event;
use Magento\Framework\EventFactory;
use Magento\Framework\ObjectManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class EventFactoryTest extends TestCase
{
    /**
     * @var EventFactory
     */
    protected $_model;

    /**
     * @var MockObject
     */
    protected $_objectManagerMock;

    /**
     * @var Event
     */
    protected $_expectedObject;

    protected function setUp(): void
    {
        $this->_objectManagerMock = $this->getMockForAbstractClass(ObjectManagerInterface::class);
        $this->_model = new EventFactory($this->_objectManagerMock);
        $this->_expectedObject = $this->getMockBuilder(Event::class)
            ->getMock();
    }

    public function testCreate()
    {
        $arguments = ['property' => 'value'];
        $this->_objectManagerMock->expects(
            $this->once()
        )->method(
            'create'
        )->with(
            Event::class,
            $arguments
        )->willReturn(
            $this->_expectedObject
        );

        $this->assertEquals($this->_expectedObject, $this->_model->create($arguments));
    }
}
