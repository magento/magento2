<?php
/**
 * Test case for \Magento\Framework\Profiler\Driver\Standard\Output\Firebug
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Profiler\Driver\Standard\Output;

class FirebugTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Profiler\Driver\Standard\Output\Firebug
     */
    protected $_output;

    /**
     * @var \Magento\Framework\App\Response\Http|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_response;

    /**
     * @var \Magento\Framework\App\RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_request;

    protected function setUp()
    {
        $this->markTestSkipped('Remove it when MAGETWO-33495 is done.');

        $this->_response = $this->getMockBuilder(
            '\Magento\Framework\App\Response\Http'
        )->setMethods(
            ['sendHeaders']
        )->disableOriginalConstructor()->getMock();

        $this->_request = $this->getMock('\Magento\Framework\App\Request\Http', ['getHeader'], [], '', false);
        $header = \Zend\Http\Header\GenericHeader::fromString('User-Agent: Mozilla/5.0 FirePHP/1.6');
        $this->_request->expects(
            $this->any()
        )->method(
            'getHeader'
        )->with(
            'User-Agent'
        )->will(
            $this->returnValue($header)
        );

        $this->_output = new \Magento\Framework\Profiler\Driver\Standard\Output\Firebug();
        $this->_output->setResponse($this->_response);
        $this->_output->setRequest($this->_request);
    }

    public function testDisplay()
    {
        $this->markTestSkipped('Remove it when task(MAGETWO-33495) will be fixed');
        $this->_response->expects($this->atLeastOnce())->method('sendHeaders');
        $this->_request->expects($this->atLeastOnce())->method('getHeader');

        $stat = include __DIR__ . '/_files/timers.php';
        $this->_output->display($stat);

        $actualHeaders = $this->_response->getHeaders();
        $this->assertNotEmpty($actualHeaders);

        $actualProtocol = false;
        $actualProfilerData = false;
        foreach ($actualHeaders as $oneHeader) {
            $headerName = $oneHeader->getFieldName();
            $headerValue = $oneHeader->getFieldValue();
            if (!$actualProtocol && $headerName == 'X-Wf-Protocol-1') {
                $actualProtocol = $headerValue;
            }
            if (!$actualProfilerData && $headerName == 'X-Wf-1-1-1-1') {
                $actualProfilerData = $headerValue;
            }
        }

        $this->assertNotEmpty($actualProtocol, 'Cannot get protocol header');
        $this->assertNotEmpty($actualProfilerData, 'Cannot get profiler header');
        $this->assertContains('Protocol/JsonStream', $actualProtocol);
        $this->assertRegExp(
            '/"Type":"TABLE","Label":"Code Profiler \(Memory usage: real - \d+, emalloc - \d+\)"/',
            $actualProfilerData
        );
        $this->assertContains(
            '[' .
            '["Timer Id","Time","Avg","Cnt","Emalloc","RealMem"],' .
            '["root","0.080000","0.080000","1","1,000","50,000"],' .
            '[". init","0.040000","0.040000","1","200","2,500"],' .
            '[". . init_store","0.020000","0.010000","2","100","2,000"],' .
            '["system","0.030000","0.015000","2","400","20,000"]' .
            ']',
            $actualProfilerData
        );
    }
}
