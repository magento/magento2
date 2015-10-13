<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Theme\Test\Unit\Model\Design\Backend;

use Magento\Framework\App\Area;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Theme\Model\Design\Backend\Theme;

class ThemeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Theme\Model\Design\Backend\Theme
     */
    protected $model;

    /**
     * @var \Magento\Framework\Model\Context|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $contextMock;

    /**
     * @var \Magento\Framework\View\DesignInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $designMock;

    protected function setUp()
    {
        $this->contextMock = $this->getMockBuilder('Magento\Framework\Model\Context')
            ->disableOriginalConstructor()
            ->getMock();
        $this->designMock = $this->getMockBuilder('Magento\Framework\View\DesignInterface')->getMock();
        $this->contextMock->expects($this->once())
            ->method('getEventDispatcher')
            ->willReturn($this->getMockBuilder('Magento\Framework\Event\ManagerInterface')->getMock());

        $this->model = (new ObjectManager($this))->getObject(
            'Magento\Theme\Model\Design\Backend\Theme',
            [
                'design' => $this->designMock,
                'context' => $this->contextMock,
            ]
        );
    }

    /**
     * @test
     * @return void
     * @covers \Magento\Theme\Model\Design\Backend\Theme::beforeSave
     * @covers \Magento\Theme\Model\Design\Backend\Theme::__construct
     */
    public function testBeforeSave()
    {
        $this->designMock->expects($this->once())
            ->method('setDesignTheme')
            ->with('some_value', Area::AREA_FRONTEND);
        $this->model->setValue('some_value');
        $this->assertInstanceOf(get_class($this->model), $this->model->beforeSave());
    }
}
