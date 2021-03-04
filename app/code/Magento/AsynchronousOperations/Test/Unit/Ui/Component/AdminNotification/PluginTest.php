<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\AsynchronousOperations\Test\Unit\Ui\Component\AdminNotification;

use Magento\Framework\AuthorizationInterface;

class PluginTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\AsynchronousOperations\Ui\Component\AdminNotification\Plugin
     */
    private $plugin;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $authorizationMock;

    protected function setUp(): void
    {
        $this->authorizationMock = $this->getMockForAbstractClass(AuthorizationInterface::class);
        $this->plugin = new \Magento\AsynchronousOperations\Ui\Component\AdminNotification\Plugin(
            $this->authorizationMock
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
        $dataProviderMock = $this->createMock(\Magento\AdminNotification\Ui\Component\DataProvider\DataProvider::class);
        $this->authorizationMock->expects($this->once())->method('isAllowed')->willReturn(true);
        $this->assertEquals($expectedResult, $this->plugin->afterGetMeta($dataProviderMock, $result));
    }
}
