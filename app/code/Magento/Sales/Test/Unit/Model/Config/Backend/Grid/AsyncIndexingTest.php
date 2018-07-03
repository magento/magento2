<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Test\Unit\Model\Config\Backend\Grid;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Unit test of backend model for global configuration value
 * 'dev/grid/async_indexing'.
 */
class AsyncIndexingTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Sales\Model\Config\Backend\Grid\AsyncIndexing
     */
    protected $object;

    /**
     * @var \Magento\Framework\App\Config|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $config;

    /**
     * @var \Magento\Framework\Model\Context|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $context;

    /**
     * @var \Magento\Framework\Event\Manager|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $eventManager;

    protected function setUp()
    {
        $objectManager = new ObjectManager($this);

        $this->config = $this->createMock(\Magento\Framework\App\Config::class);

        $this->eventManager = $this->createMock(\Magento\Framework\Event\Manager::class);

        $this->context = $this->createPartialMock(\Magento\Framework\Model\Context::class, ['getEventDispatcher']);
        $this->context->expects($this->any())->method('getEventDispatcher')->willReturn($this->eventManager);

        $this->object = $objectManager->getObject(
            \Magento\Sales\Model\Config\Backend\Grid\AsyncIndexing::class,
            [
                'config' => $this->config,
                'context' => $this->context
            ]
        );
    }

    /**
     * @param int $value
     * @param int $oldValue
     * @param string $eventName
     * @dataProvider afterSaveDataProvider
     * @return void
     */
    public function testAfterSave($value, $oldValue, $eventName)
    {
        $path = 'dev/grid/async_indexing';
        $scope = \Magento\Framework\App\Config\ScopeConfigInterface::SCOPE_TYPE_DEFAULT;

        $this->object->setData(['value' => $value, 'path' => $path, 'scope' => $scope]);

        $this->config->expects($this->once())->method('getValue')->with($path, $scope)->willReturn($oldValue);

        if ($value == $oldValue) {
            $this->eventManager->expects($this->never())->method('dispatch');
        } else {
            $this->eventManager->expects($this->once())->method('dispatch')->with($eventName);
        }

        $object = $this->object->afterSave();
        $this->assertEquals($this->object, $object);
    }

    /**
     * @return array
     */
    public function afterSaveDataProvider()
    {
        return [
            [0, 0, null],
            [1, 1, null],
            [0, 1, 'config_data_dev_grid_async_indexing_disabled'],
            [1, 0, 'config_data_dev_grid_async_indexing_enabled']
        ];
    }
}
