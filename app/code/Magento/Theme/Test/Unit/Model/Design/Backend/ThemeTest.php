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
     * @var \Magento\Framework\Model\Context
     */
    protected $context;

    /**
     * @var \Magento\Framework\View\DesignInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $designMock;

    /**
     * @var \Magento\Framework\App\Cache\TypeListInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $cacheTypeListMock;

    /**
     * @var \Magento\Framework\Event\ManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $eventManagerMock;

    /**
     * @var \Magento\Framework\App\CacheInterface | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $cacheManagerMock;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $configMock;

    protected function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->cacheManagerMock = $this->getMockBuilder('Magento\Framework\App\CacheInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $this->eventManagerMock = $this->getMockBuilder('Magento\Framework\Event\ManagerInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $this->context = $objectManager->getObject(
            'Magento\Framework\Model\Context',
            [
                'cacheManager' => $this->cacheManagerMock,
                'eventDispatcher' => $this->eventManagerMock,
            ]
        );

        $this->designMock = $this->getMockBuilder('Magento\Framework\View\DesignInterface')->getMock();
        $this->cacheTypeListMock = $this->getMockBuilder('Magento\Framework\App\Cache\TypeListInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $this->configMock = $this->getMockBuilder('Magento\Framework\App\Config\ScopeConfigInterface')->getMock();


        $this->model = $objectManager->getObject(
            'Magento\Theme\Model\Design\Backend\Theme',
            [
                'design' => $this->designMock,
                'context' => $this->context,
                'cacheTypeList' => $this->cacheTypeListMock,
                'config' => $this->configMock,
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

    /**
     * @param int $callNumber
     * @param string $oldValue
     * @dataProvider afterSaveDataProvider
     */
    public function testAfterSave($callNumber, $oldValue)
    {
        $this->cacheTypeListMock->expects($this->exactly($callNumber))
            ->method('invalidate');
        $this->cacheManagerMock->expects($this->exactly($callNumber))
            ->method('clean');
        $this->configMock->expects($this->any())
            ->method('getValue')
            ->willReturn($oldValue);
        if ($callNumber) {
            $this->eventManagerMock->expects($this->at(3))
                ->method('dispatch')
                ->with('adminhtml_cache_flush_system');
        }
        $this->model->setValue('some_value');
        $this->assertInstanceOf(get_class($this->model), $this->model->afterSave());
    }

    public function afterSaveDataProvider()
    {
        return [
            [0, 'some_value'],
            [1, 'other_value'],
        ];
    }
}
