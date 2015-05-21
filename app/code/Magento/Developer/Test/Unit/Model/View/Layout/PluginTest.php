<?php
/***
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Developer\Test\Unit\Model\View\Layout;

use Magento\Framework\App\State;

class PluginTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Error message for exception that gets thrown by the closure
     */
    const EXCEPTION_MESSAGE = 'Error: AI is growing too strong';

    /** @var \Magento\Framework\App\State | \PHPUnit_Framework_MockObject_MockObject */
    private $appStateMock;

    /** @var  \Magento\Framework\View\Layout | \PHPUnit_Framework_MockObject_MockObject */
    private $loggerMock;

    /** @var  \Magento\Developer\Model\View\Layout\Plugin */
    private $model;

    /** @var  \Magento\Framework\View\Layout */
    private $subjectMock;

    /** @var  \Closure */
    private $exceptionCallBack;

    public function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->loggerMock = $this->getMockBuilder('\Psr\Log\LoggerInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $this->appStateMock = $this->getMockBuilder('\Magento\Framework\App\State')
            ->disableOriginalConstructor()
            ->getMock();
        $this->subjectMock =  $this->getMockBuilder('\Magento\Framework\View\Layout')
            ->disableOriginalConstructor()
            ->getMock();

        // Callback function to pass to the plugin method
        $this->exceptionCallBack =
            function ()
            {
                throw new \Exception(self::EXCEPTION_MESSAGE);
            };

        $mocks = [
            'appState' => $this->appStateMock,
            'logger' => $this->loggerMock
        ];
        $this->model = $objectManager->getObject('\Magento\Developer\Model\View\Layout\Plugin', $mocks);
    }

    public function testNoException()
    {
        $name = 'Ava';
        $callback =
            function ($name)
            {
                return $name;
            };

        $this->assertSame(
            $name,
            $this->model->aroundRenderNonCachedElement($this->subjectMock, $callback, $name)
        );
        $this->loggerMock->expects($this->never())->method('critical');
        $this->appStateMock->expects($this->never())->method('getMode');
    }

    public function testExceptionDevMode()
    {
        $this->appStateMock->expects($this->once())->method('getMode')->willReturn('NOT_DEVELOPER_MODE');
        $this->loggerMock->expects($this->once())->method('critical')->with(self::EXCEPTION_MESSAGE);
        $this->model->aroundRenderNonCachedElement($this->subjectMock, $this->exceptionCallBack, 'name');
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage \Magento\Developer\Test\Unit\Model\View\Layout\PluginTest::EXCEPTION_MESSAGE
     */
    public function testExceptionNonDevMode()
    {
        $this->appStateMock->expects($this->once())->method('getMode')->willReturn(State::MODE_DEVELOPER);
        $this->loggerMock->expects($this->never())->method('critical');
        $this->model->aroundRenderNonCachedElement($this->subjectMock, $this->exceptionCallBack, 'name');
    }
}
