<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

/**
 * Test design model
 */
namespace Magento\Theme\Test\Unit\Model;

use Magento\Framework\App\CacheInterface;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\Stdlib\DateTime;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\DesignInterface;
use Magento\Theme\Model\Design;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class DesignTest extends TestCase
{
    /**
     * @var Design
     */
    protected $model;

    /**
     * @var CacheInterface|MockObject
     */
    protected $cacheManager;

    /**
     * @var TimezoneInterface|MockObject
     */
    protected $localeDate;

    /**
     * @var DateTime|MockObject
     */
    protected $dateTime;

    /**
     * @var AbstractResource|MockObject
     */
    protected $resource;

    /**
     * @var SerializerInterface|MockObject
     */
    private $serializerMock;

    protected function setUp(): void
    {
        $context = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->localeDate = $this->getMockBuilder(
            TimezoneInterface::class
        )->getMock();
        $this->dateTime = $this->getMockBuilder(DateTime::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->resource = $this->getMockBuilder(\Magento\Theme\Model\ResourceModel\Design::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->cacheManager = $this->getMockBuilder(CacheInterface::class)
            ->getMock();

        $context->expects($this->any())
            ->method('getCacheManager')
            ->willReturn($this->cacheManager);

        $this->serializerMock = $this->getMockForAbstractClass(SerializerInterface::class);

        $objectManager = new ObjectManager($this);
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
        $cacheId = 'design_change_' . hash('md5', (string)$storeId . $date);
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
        $cacheId = 'design_change_' . hash('md5', (string)$storeId . $date);
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
        $design = $this->getMockBuilder(DesignInterface::class)
            ->getMock();

        $this->model->setDesign('test');
        /** @var $design \Magento\Framework\View\DesignInterface */
        $this->assertInstanceOf(get_class($this->model), $this->model->changeDesign($design));
    }
}
