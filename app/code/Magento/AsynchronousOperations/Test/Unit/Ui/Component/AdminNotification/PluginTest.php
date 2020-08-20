<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AsynchronousOperations\Test\Unit\Ui\Component\AdminNotification;

use Magento\AdminNotification\Ui\Component\DataProvider\DataProvider;
use Magento\AsynchronousOperations\Ui\Component\AdminNotification\Plugin;
use Magento\Framework\AuthorizationInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class PluginTest extends TestCase
{
    /**
     * @var Plugin
     */
    private $plugin;

    /**
     * @var MockObject
     */
    private $authorizationMock;

    protected function setUp(): void
    {
        $this->authorizationMock = $this->getMockForAbstractClass(AuthorizationInterface::class);
        $this->plugin = new Plugin(
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
        $dataProviderMock = $this->createMock(DataProvider::class);
        $this->authorizationMock->expects($this->once())->method('isAllowed')->willReturn(true);
        $this->assertEquals($expectedResult, $this->plugin->afterGetMeta($dataProviderMock, $result));
    }
}
