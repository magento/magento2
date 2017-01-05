<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\AsynchronousOperations\Test\Unit\Ui\Component\AdminNotification;

use Magento\Framework\AuthorizationInterface;

class PluginTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\AsynchronousOperations\Ui\Component\AdminNotification\Plugin
     */
    private $plugin;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $authorizationMock;

    protected function setUp()
    {
        $this->authorizationMock = $this->getMock(AuthorizationInterface::class);
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
        $dataProviderMock = $this->getMock(
            \Magento\AdminNotification\Ui\Component\DataProvider\DataProvider::class,
            [],
            [],
            '',
            false
        );
        $this->authorizationMock->expects($this->once())->method('isAllowed')->willReturn(true);
        $this->assertEquals($expectedResult, $this->plugin->afterGetMeta($dataProviderMock, $result));
    }
}
