<?php
/**
 * Test class for \Magento\Framework\Profiler\Driver\Standard\Output\Firebug
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Profiler\Test\Unit\Driver\Standard\Output;

class FirebugTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Profiler\Driver\Standard\Output\Firebug
     */
    protected $_output;

    protected function setUp()
    {
        $this->_output = new \Magento\Framework\Profiler\Driver\Standard\Output\Firebug();
    }

    protected function tearDown()
    {
        ob_end_flush();
    }

    public function testGetAndSetRequest()
    {
        $this->assertInstanceOf('Zend_Controller_Request_Abstract', $this->_output->getRequest());
        $request = $this->getMock('Zend_Controller_Request_Abstract');
        $this->_output->setRequest($request);
        $this->assertSame($request, $this->_output->getRequest());
    }

    public function testGetAndSetResponse()
    {
        $this->assertInstanceOf('Zend\Http\PhpEnvironment\Response', $this->_output->getResponse());
        $response = $this->getMock('Magento\Framework\App\Response\Http', null, [], '', false);
        $this->_output->setResponse($response);
        $this->assertSame($response, $this->_output->getResponse());
    }
}
