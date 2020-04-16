<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\AsynchronousOperations\Test\Unit\Ui\Component\AdminNotification;

use Magento\Framework\AuthorizationInterface;
use Magento\AsynchronousOperations\Model\AccessManager;
use Magento\AdminNotification\Ui\Component\DataProvider\DataProvider;
use Magento\AsynchronousOperations\Ui\Component\AdminNotification\Plugin;

class PluginTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Plugin
     */
    private $plugin;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $accessManagerMock;

    protected function setUp()
    {
        $this->accessManagerMock = $this->createMock(AccessManager::class);
        $this->plugin = new Plugin(
            $this->accessManagerMock
        );
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
        $this->accessManagerMock->expects($this->once())->method('isOwnActionsAllowed')->willReturn(true);
        $this->assertEquals($expectedResult, $this->plugin->afterGetMeta($dataProviderMock, $result));
    }
}
