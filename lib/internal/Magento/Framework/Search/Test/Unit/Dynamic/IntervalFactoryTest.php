<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Search\Test\Unit\Dynamic;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Search\Dynamic\IntervalInterface;
use Magento\Framework\App\ScopeInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class IntervalFactoryTest extends \PHPUnit\Framework\TestCase
{
    const CONFIG_PATH = 'config_path';
    const INTERVAL = 'some_interval';

    /**
     * @var \Magento\Framework\ObjectManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $objectManager;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $scopeConfig;

    /**
     * @var \Magento\Framework\Search\Dynamic\IntervalInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $interval;

    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    private $helper;

    /**
     * SetUp method
     */
    protected function setUp()
    {
        $this->helper = new ObjectManager($this);

        $this->objectManager = $this->getMockBuilder(\Magento\Framework\ObjectManagerInterface::class)
            ->setMethods(['create', 'get', 'configure'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->scopeConfig = $this->getMockBuilder(\Magento\Framework\App\Config\ScopeConfigInterface::class)
            ->setMethods(['getValue'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->interval = $this->getMockBuilder(\Magento\Framework\Search\Dynamic\IntervalInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
    }

    /**
     * Test for method Create
     */
    public function testCreate()
    {
        $this->scopeConfig->expects($this->once())
            ->method('getValue')
            ->with(self::CONFIG_PATH, ScopeConfigInterface::SCOPE_TYPE_DEFAULT)
            ->willReturn(self::CONFIG_PATH . 't');
        $this->objectManager->expects($this->once())
            ->method('create')
            ->with(self::INTERVAL)
            ->willReturn($this->interval);

        $result = $this->factoryCreate();

        $this->assertEquals($this->interval, $result);
    }

    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage Interval not found by config t
     */
    public function testCreateIntervalNotFoundException()
    {
        $this->scopeConfig->expects($this->once())
            ->method('getValue')
            ->with(self::CONFIG_PATH, ScopeConfigInterface::SCOPE_TYPE_DEFAULT)
            ->willReturn('t');

        $this->factoryCreate();
    }

    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage Interval not instance of interface \Magento\Framework\Search\Dynamic\IntervalInterface
     */
    public function testCreateIntervalNotImplementedInterfaceException()
    {
        $this->scopeConfig->expects($this->once())
            ->method('getValue')
            ->with(self::CONFIG_PATH, ScopeConfigInterface::SCOPE_TYPE_DEFAULT)
            ->willReturn(self::CONFIG_PATH . 't');
        $this->objectManager->expects($this->once())
            ->method('create')
            ->with(self::INTERVAL)
            ->willReturn($this->objectManager);

        $this->factoryCreate();
    }

    /**
     * @return IntervalInterface
     */
    private function factoryCreate()
    {
        /** @var \Magento\Framework\Search\Dynamic\IntervalFactory $factory */
        $factory = $this->helper->getObject(
            \Magento\Framework\Search\Dynamic\IntervalFactory::class,
            [
                'objectManager' => $this->objectManager,
                'scopeConfig' => $this->scopeConfig,
                'configPath' => self::CONFIG_PATH,
                'intervals' => [self::CONFIG_PATH . 't' => self::INTERVAL]
            ]
        );

        return $factory->create();
    }
}
