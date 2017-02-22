<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Test\Unit\Action\Plugin;

class DesignTest extends \PHPUnit_Framework_TestCase
{
    public function testAroundDispatch()
    {
        $subjectMock = $this->getMock('Magento\Framework\App\Action\Action', [], [], '', false);
        $designLoaderMock = $this->getMock('Magento\Framework\View\DesignLoader', [], [], '', false);
        $closureMock = function () {
            return 'Expected';
        };
        $requestMock = $this->getMock('Magento\Framework\App\RequestInterface');
        $plugin = new \Magento\Framework\App\Action\Plugin\Design($designLoaderMock);
        $designLoaderMock->expects($this->once())->method('load');
        $this->assertEquals('Expected', $plugin->aroundDispatch($subjectMock, $closureMock, $requestMock));
    }
}
