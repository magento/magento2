<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Test\Unit\Model\Design\Backend;

use Magento\Theme\Model\Design\Backend\Exceptions;
use Magento\Framework\App\Area;

class ExceptionsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Theme\Model\Design\Backend\Exceptions
     */
    protected $model;

    /**
     * @var \Magento\Framework\Model\Context|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $context;

    /**
     * @var \Magento\Framework\Registry|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $registry;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $config;

    /**
     * @var \Magento\Framework\View\DesignInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $design;

    /**
     * @var \Magento\Theme\Model\Resource\Design|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resource;

    /**
     * @var \Magento\Theme\Model\Resource\Design\Collection|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resourceCollection;

    protected function setUp()
    {
        $this->context = $this->getMockBuilder('Magento\Framework\Model\Context')
            ->disableOriginalConstructor()
            ->getMock();
        $this->registry = $this->getMockBuilder('Magento\Framework\Registry')
            ->disableOriginalConstructor()
            ->getMock();
        $this->config = $this->getMockBuilder('Magento\Framework\App\Config\ScopeConfigInterface')->getMock();
        $this->design = $this->getMockBuilder('Magento\Framework\View\DesignInterface')->getMock();
        $this->resource = $this->getMockBuilder('Magento\Theme\Model\Resource\Design')
            ->disableOriginalConstructor()
            ->getMock();
        $this->resourceCollection = $this->getMockBuilder('Magento\Theme\Model\Resource\Design\Collection')
            ->disableOriginalConstructor()
            ->getMock();
        $this->context->expects($this->once())
            ->method('getEventDispatcher')
            ->willReturn($this->getMockBuilder('Magento\Framework\Event\ManagerInterface')->getMock());

        $this->model = new Exceptions(
            $this->context,
            $this->registry,
            $this->config,
            $this->design,
            $this->resource,
            $this->resourceCollection
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
        $value = ['__empty' => '', 'test' => ['search' => '1qwe', 'value' => '#val#', 'regexp' => '[a-zA-Z0-9]*']];
        $this->design->expects($this->once())
            ->method('setDesignTheme')
            ->with('#val#', Area::AREA_FRONTEND);
        $this->model->setValue($value);
        $this->model->beforeSave();
    }
}
