<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Test\Unit\Action\Plugin;

class DesignTest extends \PHPUnit_Framework_TestCase
{
    public function testAroundDispatch()
    {
        $subjectMock = $this->getMock('Magento\Framework\App\Action\Action', [], [], '', false);
        $designLoaderMock = $this->getMock('Magento\Framework\View\DesignLoader', [], [], '', false);
        $messageManagerMock = $this->getMock('Magento\Framework\Message\ManagerInterface', [], [], '', false);
        $requestMock = $this->getMock('Magento\Framework\App\RequestInterface');
        $plugin = new \Magento\Framework\App\Action\Plugin\Design($designLoaderMock, $messageManagerMock);
        $designLoaderMock->expects($this->once())->method('load');
        $plugin->beforeDispatch($subjectMock, $requestMock);
    }
}
