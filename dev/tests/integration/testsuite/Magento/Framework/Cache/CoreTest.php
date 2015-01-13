<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * \Magento\Framework\Cache\Core test case
 */
namespace Magento\Framework\Cache;

class CoreTest extends \PHPUnit_Framework_TestCase
{
    public function testSetBackendSuccess()
    {
        $mockBackend = $this->getMock('Zend_Cache_Backend_File');
        $config = [
            'backend_decorators' => [
                'test_decorator' => [
                    'class' => 'Magento\Framework\Cache\Backend\Decorator\Compression',
                    'options' => ['compression_threshold' => '100'],
                ],
            ],
        ];

        $core = new \Magento\Framework\Cache\Core($config);
        $core->setBackend($mockBackend);

        $this->assertInstanceOf('Magento\Framework\Cache\Backend\Decorator\AbstractDecorator', $core->getBackend());
    }

    /**
     * @expectedException \Zend_Cache_Exception
     */
    public function testSetBackendException()
    {
        $mockBackend = $this->getMock('Zend_Cache_Backend_File');
        $config = ['backend_decorators' => ['test_decorator' => ['class' => 'Zend_Cache_Backend']]];

        $core = new \Magento\Framework\Cache\Core($config);
        $core->setBackend($mockBackend);
    }
}
