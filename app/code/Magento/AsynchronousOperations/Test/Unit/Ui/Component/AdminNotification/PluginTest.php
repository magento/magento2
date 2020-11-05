<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AsynchronousOperations\Test\Unit\Ui\Component\AdminNotification;

use Magento\AdminNotification\Ui\Component\DataProvider\DataProvider;
use Magento\AsynchronousOperations\Ui\Component\AdminNotification\Plugin;
use Magento\AsynchronousOperations\Model\AccessManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class PluginTest extends TestCase
{
    /**
     * @var Plugin
     */
    private $plugin;

    /**
     * @var AccessManager|MockObject
     */
    private $accessMangerMock;

    protected function setUp(): void
    {
        $this->accessMangerMock = $this->createMock(AccessManager::class);
        $this->plugin = new Plugin($this->accessMangerMock);
    }

    public function testAfterGetMeta()
    {
        $result = [];
        $expectedResult = [
            'columns' => [
                'arguments' => [
                    'data' => [
                        'config' => [
                            'isAllowed' => true
                        ]
                    ]
                ]
            ]
        ];
        $dataProviderMock = $this->createMock(DataProvider::class);
        $this->accessMangerMock->expects($this->once())
            ->method('isOwnActionsAllowed')
            ->willReturn(true);

        $this->assertEquals($expectedResult, $this->plugin->afterGetMeta($dataProviderMock, $result));
    }
}
