<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Test\Unit\Action\Plugin;

class DesignTest extends \PHPUnit_Framework_TestCase
{
    public function testAroundDispatch()
    {
        $subjectMock = $this->getMock(\Magento\Framework\App\Action\Action::class, [], [], '', false);
        $designLoaderMock = $this->getMock(\Magento\Framework\View\DesignLoader::class, [], [], '', false);
        $messageManagerMock = $this->getMock(\Magento\Framework\Message\ManagerInterface::class, [], [], '', false);
        $requestMock = $this->getMock(\Magento\Framework\App\RequestInterface::class);
        $plugin = new \Magento\Framework\App\Action\Plugin\Design($designLoaderMock, $messageManagerMock);
        $designLoaderMock->expects($this->once())->method('load');
        $plugin->beforeDispatch($subjectMock, $requestMock);
    }
}
