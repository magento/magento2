<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Model\Config\Backend\Grid;

use Magento\Framework\App\Config;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Event\Manager;
use Magento\Framework\Model\Context;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Sales\Model\Config\Backend\Grid\AsyncIndexing;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Unit test of backend model for global configuration value
 * 'dev/grid/async_indexing'.
 */
class AsyncIndexingTest extends TestCase
{
    /**
     * @var AsyncIndexing
     */
    protected $object;

    /**
     * @var Config|MockObject
     */
    protected $config;

    /**
     * @var Context|MockObject
     */
    protected $context;

    /**
     * @var Manager|MockObject
     */
    protected $eventManager;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);

        $this->config = $this->createMock(Config::class);

        $this->eventManager = $this->createMock(Manager::class);

        $this->context = $this->createPartialMock(Context::class, ['getEventDispatcher']);
        $this->context->expects($this->any())->method('getEventDispatcher')->willReturn($this->eventManager);

        $this->object = $objectManager->getObject(
            AsyncIndexing::class,
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
        $scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT;

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
