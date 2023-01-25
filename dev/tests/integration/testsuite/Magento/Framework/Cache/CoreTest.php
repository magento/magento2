<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * \Magento\Framework\Cache\Core test case
 */
namespace Magento\Framework\Cache;

class CoreTest extends \PHPUnit\Framework\TestCase
{
    public function testSetBackendSuccess()
    {
        $mockBackend = $this->createMock(\Zend_Cache_Backend_File::class);
        $config = [
            'backend_decorators' => [
                'test_decorator' => [
                    'class' => \Magento\Framework\Cache\Backend\Decorator\Compression::class,
                    'options' => ['compression_threshold' => '100'],
                ],
            ],
        ];

        $core = new \Magento\Framework\Cache\Core($config);
        $core->setBackend($mockBackend);

        $this->assertInstanceOf(
            \Magento\Framework\Cache\Backend\Decorator\AbstractDecorator::class,
            $core->getBackend()
        );
    }

    /**
     */
    public function testSetBackendException()
    {
        $this->expectException(\Zend_Cache_Exception::class);

        $mockBackend = $this->createMock(\Zend_Cache_Backend_File::class);
        $config = ['backend_decorators' => ['test_decorator' => ['class' => 'Zend_Cache_Backend']]];

        $core = new \Magento\Framework\Cache\Core($config);
        $core->setBackend($mockBackend);
    }
}
