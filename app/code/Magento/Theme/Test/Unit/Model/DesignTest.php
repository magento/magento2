<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Test design model
 */
namespace Magento\Theme\Test\Unit\Model;

use Magento\Theme\Model\Design;

class DesignTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Design
     */
    protected $model;

    /**
     * @var \Magento\Framework\App\CacheInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $cacheManager;

    /**
     * @var \Magento\Framework\Registry|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $registry;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $localeDate;

    /**
     * @var \Magento\Framework\Stdlib\DateTime|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $dateTime;

    /**
     * @var \Magento\Framework\Model\ResourceModel\AbstractResource|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resource;

    /**
     * @var \Magento\Framework\Data\Collection\AbstractDb|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resourceCollection;

    protected function setUp()
    {
        $context = $this->getMockBuilder('Magento\Framework\Model\Context')
            ->disableOriginalConstructor()
            ->getMock();
        $this->registry = $this->getMockBuilder('Magento\Framework\Registry')->disableOriginalConstructor()->getMock();
        $this->localeDate = $this->getMockBuilder('Magento\Framework\Stdlib\DateTime\TimezoneInterface')->getMock();
        $this->dateTime = $this->getMockBuilder('Magento\Framework\Stdlib\DateTime')
            ->disableOriginalConstructor()
            ->getMock();
        $this->resource = $this->getMockBuilder('Magento\Theme\Model\ResourceModel\Design')
            ->disableOriginalConstructor()
            ->getMock();
        $this->resourceCollection = $this->getMockBuilder('Magento\Theme\Model\ResourceModel\Design\Collection')
            ->disableOriginalConstructor()
            ->getMock();
        $this->cacheManager = $this->getMockBuilder('Magento\Framework\App\CacheInterface')->getMock();

        $context->expects($this->any())
            ->method('getCacheManager')
            ->willReturn($this->cacheManager);

        /**
         * @var $context \Magento\Framework\Model\Context
         */
        $this->model = new Design(
            $context,
            $this->registry,
            $this->localeDate,
            $this->dateTime,
            $this->resource,
            $this->resourceCollection
        );
    }

    protected function tearDown()
    {
        $this->model = null;
    }

    /**
     * @test
     * @return void
     * @covers \Magento\Theme\Model\Design::loadChange
     */
    public function testLoadChange()
    {
        $storeId = 1;
        $localDate = '2\28\2000';
        $date = '28-02-2000';
        $cacheId = 'design_change_' . md5($storeId . $date);
        $this->localeDate->expects($this->once())
            ->method('scopeTimeStamp')
            ->with($storeId)
            ->willReturn($localDate);
        $this->dateTime->expects($this->once())
            ->method('formatDate')
            ->with($localDate, false)
            ->willReturn($date);
        $this->cacheManager->expects($this->once())
            ->method('load')
            ->with($cacheId)
            ->willReturn(false);
        $this->resource->expects($this->once())
            ->method('loadChange')
            ->with($storeId, $date)
            ->willReturn(false);
        $this->cacheManager->expects($this->once())
            ->method('save')
            ->with(serialize([]), $cacheId, [Design::CACHE_TAG], 86400)
            ->willReturnSelf();

        $this->assertInstanceOf(get_class($this->model), $this->model->loadChange($storeId));
    }

    /**
     * @test
     * @return void
     * @covers \Magento\Theme\Model\Design::loadChange
     * @covers \Magento\Theme\Model\Design::__construct
     * @covers \Magento\Theme\Model\Design::_construct
     */
    public function testLoadChangeFromCache()
    {
        $storeId = 1;
        $localDate = '2\28\2000';
        $date = '28-02-2000';
        $cacheId = 'design_change_' . md5($storeId . $date);
        $this->localeDate->expects($this->once())
            ->method('scopeTimeStamp')
            ->with($storeId)
            ->willReturn($localDate);
        $this->dateTime->expects($this->once())
            ->method('formatDate')
            ->with($localDate, false)
            ->willReturn($date);
        $this->cacheManager->expects($this->once())
            ->method('load')
            ->with($cacheId)
            ->willReturn(serialize(['test' => 'data']));

        $this->assertInstanceOf(get_class($this->model), $this->model->loadChange($storeId));
    }

    /**
     * @test
     * @return void
     * @covers \Magento\Theme\Model\Design::getIdentities
     * @covers \Magento\Theme\Model\Design::__construct
     * @covers \Magento\Theme\Model\Design::_construct
     */
    public function testGetIdentities()
    {
        $id = 1;
        $this->model->setId($id);
        $this->assertEquals([Design::CACHE_TAG . '_' . $id], $this->model->getIdentities());
    }

    /**
     * @test
     * @return void
     * @covers \Magento\Theme\Model\Design::changeDesign
     * @covers \Magento\Theme\Model\Design::__construct
     * @covers \Magento\Theme\Model\Design::_construct
     */
    public function testChangeDesign()
    {
        $design = $this->getMockBuilder('Magento\Framework\View\DesignInterface')->getMock();

        $this->model->setDesign('test');
        /** @var $design \Magento\Framework\View\DesignInterface */
        $this->assertInstanceOf(get_class($this->model), $this->model->changeDesign($design));
    }
}
