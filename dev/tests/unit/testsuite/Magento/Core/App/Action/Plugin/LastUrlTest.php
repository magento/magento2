<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Core\App\Action\Plugin;

class LastUrlTest extends \PHPUnit_Framework_TestCase
{
    public function testAfterDispatch()
    {
        $session = $this->getMock('\Magento\Framework\Session\Generic', ['setLastUrl'], [], '', false);
        $subjectMock = $this->getMock('Magento\Framework\App\Action\Action', [], [], '', false);
        $closureMock = function () {
            return 'result';
        };
        $requestMock = $this->getMock('Magento\Framework\App\RequestInterface');
        $url = $this->getMock('\Magento\Framework\Url', [], [], '', false);
        $plugin = new \Magento\Core\App\Action\Plugin\LastUrl($session, $url);
        $session->expects($this->once())->method('setLastUrl')->with('http://example.com');
        $url->expects(
            $this->once()
        )->method(
            'getUrl'
        )->with(
            '*/*/*',
            ['_current' => true]
        )->will(
            $this->returnValue('http://example.com')
        );
        $this->assertEquals('result', $plugin->aroundDispatch($subjectMock, $closureMock, $requestMock));
    }
}
