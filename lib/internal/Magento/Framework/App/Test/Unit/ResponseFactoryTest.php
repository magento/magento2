<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\App\Test\Unit;

use Magento\Framework\App\ResponseFactory;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\ObjectManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ResponseFactoryTest extends TestCase
{
    /**
     * @var ResponseFactory
     */
    protected $_model;

    /**
     * @var MockObject
     */
    protected $_objectManagerMock;

    /**
     * @var ResponseInterface
     */
    protected $_expectedObject;

    protected function setUp(): void
    {
        $this->_objectManagerMock = $this->getMockForAbstractClass(ObjectManagerInterface::class);
        $this->_model = new ResponseFactory($this->_objectManagerMock);
    }

    public function testCreate()
    {
        $this->_expectedObject = $this->getMockBuilder(ResponseInterface::class)
            ->getMock();
        $arguments = [['property' => 'value']];
        $this->_objectManagerMock->expects(
            $this->once()
        )->method(
            'create'
        )->with(
            ResponseInterface::class,
            $arguments
        )->willReturn(
            $this->_expectedObject
        );

        $this->assertEquals($this->_expectedObject, $this->_model->create($arguments));
    }
}
