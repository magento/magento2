<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Test design model
 */
namespace Magento\Theme\Test\Unit\Model;

use Magento\Framework\Serialize\SerializerInterface;
use Magento\Theme\Model\Design;

class DesignTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Design
     */
    protected $model;

    /**
     * @var \Magento\Framework\App\CacheInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $cacheManager;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $localeDate;

    /**
     * @var \Magento\Framework\Stdlib\DateTime|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $dateTime;

    /**
     * @var \Magento\Framework\Model\ResourceModel\AbstractResource|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $resource;

    /**
     * @var SerializerInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $serializerMock;

    protected function setUp(): void
    {
        $context = $this->getMockBuilder(\Magento\Framework\Model\Context::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->localeDate = $this->getMockBuilder(
            \Magento\Framework\Stdlib\DateTime\TimezoneInterface::class
        )->getMock();
        $this->dateTime = $this->getMockBuilder(\Magento\Framework\Stdlib\DateTime::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->resource = $this->getMockBuilder(\Magento\Theme\Model\ResourceModel\Design::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->cacheManager = $this->getMockBuilder(\Magento\Framework\App\CacheInterface::class)->getMock();

        $context->expects($this->any())
            ->method('getCacheManager')
            ->willReturn($this->cacheManager);

        $this->serializerMock = $this->getMockForAbstractClass(SerializerInterface::class);

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->model = $objectManager->getObject(
            Design::class,
            [
                'context' => $context,
                'localeDate' => $this->localeDate,
                'dateTime' => $this->dateTime,
                'resource' => $this->resource,
                'serializer' => $this->serializerMock,
            ]
        );
    }

    protected function tearDown(): void
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
        $this->serializerMock->expects($this->once())
            ->method('serialize')
            ->willReturn('serializedData');
        $this->cacheManager->expects($this->once())
            ->method('save')
            ->with('serializedData', $cacheId, [Design::CACHE_TAG], 86400)
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
            ->willReturn('serializedData');
        $data = ['test' => 'data'];
        $this->serializerMock->expects($this->once())
            ->method('unserialize')
            ->with('serializedData')
            ->willReturn($data);

        $change = $this->model->loadChange($storeId);
        $this->assertInstanceOf(get_class($this->model), $change);
        $this->assertEquals($data, $change->getData());
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
        $design = $this->getMockBuilder(\Magento\Framework\View\DesignInterface::class)->getMock();

        $this->model->setDesign('test');
        /** @var $design \Magento\Framework\View\DesignInterface */
        $this->assertInstanceOf(get_class($this->model), $this->model->changeDesign($design));
    }
}
