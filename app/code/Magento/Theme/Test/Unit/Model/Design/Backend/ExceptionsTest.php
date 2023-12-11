<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Theme\Test\Unit\Model\Design\Backend;

use Magento\Framework\App\Area;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Model\Context;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\DesignInterface;
use Magento\Theme\Model\Design\Backend\Exceptions;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ExceptionsTest extends TestCase
{
    /**
     * @var Exceptions
     */
    protected $model;

    /**
     * @var Context|MockObject
     */
    protected $contextMock;

    /**
     * @var DesignInterface|MockObject
     */
    protected $designMock;

    protected function setUp(): void
    {
        $this->contextMock = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->designMock = $this->getMockBuilder(DesignInterface::class)
            ->getMock();
        $this->contextMock->expects($this->once())
            ->method('getEventDispatcher')
            ->willReturn($this->getMockBuilder(ManagerInterface::class)
            ->getMock());
        $serializerMock = $this->getMockBuilder(Json::class)
            ->getMock();
        $this->model = (new ObjectManager($this))->getObject(
            Exceptions::class,
            [
                'context' => $this->contextMock,
                'design' => $this->designMock,
                'serializer' => $serializerMock,
            ]
        );
    }

    /**
     * @test
     * @return void
     * @covers \Magento\Theme\Model\Design\Backend\Exceptions::beforeSave
     * @covers \Magento\Theme\Model\Design\Backend\Exceptions::_composeRegexp
     * @covers \Magento\Theme\Model\Design\Backend\Exceptions::_isRegexp
     * @covers \Magento\Theme\Model\Design\Backend\Exceptions::__construct
     */
    public function testBeforeSave()
    {
        $value = ['test' => ['search' => '1qwe', 'value' => '#val#', 'regexp' => '[a-zA-Z0-9]*']];
        $this->designMock->expects($this->once())
            ->method('setDesignTheme')
            ->with('#val#', Area::AREA_FRONTEND);
        $this->model->setValue($value);
        $this->model->beforeSave();
    }

    public function testAfterLoad()
    {
        $this->model->setValue(
            [
                [
                    'value' => 'value',
                    'search' => 'qwe',
                    'record_id' => 1
                ],
            ]
        );
        $this->model->afterLoad();
        $this->assertEquals(
            [
                [
                    'value' => 'value',
                    'search' => 'qwe',
                ],
            ],
            $this->model->getValue()
        );
    }
}
