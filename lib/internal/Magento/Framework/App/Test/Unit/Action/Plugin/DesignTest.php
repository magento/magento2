<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Test\Unit\Action\Plugin;

class DesignTest extends \PHPUnit\Framework\TestCase
{
    public function testBeforeExecute()
    {
        $subjectMock = $this->createMock(\Magento\Framework\App\Action\Action::class);
        $designLoaderMock = $this->createMock(\Magento\Framework\View\DesignLoader::class);
        $messageManagerMock = $this->createMock(\Magento\Framework\Message\ManagerInterface::class);
        $plugin = new \Magento\Framework\App\Action\Plugin\Design($designLoaderMock, $messageManagerMock);
        $designLoaderMock->expects($this->once())->method('load');
        $plugin->beforeExecute($subjectMock);
    }
}
