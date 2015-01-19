<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Test;

class RequestTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\TestFramework\Request
     */
    protected $_model = null;

    protected function setUp()
    {
        $this->_model = new \Magento\TestFramework\Request(
            $this->getMock('Magento\Framework\App\Route\ConfigInterface\Proxy', [], [], '', false),
            $this->getMock('Magento\Framework\App\Request\PathInfoProcessorInterface', [], [], '', false),
            $this->getMock('Magento\Framework\Stdlib\Cookie\CookieReaderInterface'),
            $this->getMock('Magento\Framework\ObjectManagerInterface')
        );
    }

    public function testGetHttpHost()
    {
        $this->assertEquals('localhost', $this->_model->getHttpHost());
        $this->assertEquals('localhost', $this->_model->getHttpHost(false));
    }

    public function testSetGetServer()
    {
        $this->assertSame([], $this->_model->getServer());
        $this->assertSame($this->_model, $this->_model->setServer(['test' => 'value', 'null' => null]));
        $this->assertSame(['test' => 'value', 'null' => null], $this->_model->getServer());
        $this->assertEquals('value', $this->_model->getServer('test'));
        $this->assertSame(null, $this->_model->getServer('non-existing'));
        $this->assertSame('default', $this->_model->getServer('non-existing', 'default'));
        $this->assertSame(null, $this->_model->getServer('null'));
    }
}
