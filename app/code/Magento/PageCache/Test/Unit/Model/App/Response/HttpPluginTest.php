<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\PageCache\Test\Unit\Model\App\Response;

use Magento\PageCache\Model\App\Response\HttpPlugin;

class HttpPluginTest extends \PHPUnit_Framework_TestCase
{
    public function testBeforeSendResponse()
    {
        /** @var \Magento\Framework\App\Response\Http|\PHPUnit_Framework_MockObject_MockObject $responseMock */
        $responseMock = $this->getMockBuilder('Magento\Framework\App\Response\Http')
            ->disableOriginalConstructor()
            ->getMock();
        $responseMock->expects($this->once())->method('sendVary');
        $plugin = new HttpPlugin();
        $plugin->beforeSendResponse($responseMock);
    }
}
