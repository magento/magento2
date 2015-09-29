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
    /**
     * @param bool $usePageCache
     * @param int $sendVaryCalled
     *
     * @dataProvider beforeSendResponseDataProvider
     */
    public function testBeforeSendResponse($usePageCache, $sendVaryCalled)
    {
        /** @var \Magento\Framework\App\Response\Http | \PHPUnit_Framework_MockObject_MockObject $responseMock */
        $responseMock = $this->getMock('Magento\Framework\App\Response\Http', [], [], '', false);
        $responseMock->expects($this->exactly($sendVaryCalled))
            ->method('sendVary');
        /** @var \Magento\Framework\Registry | \PHPUnit_Framework_MockObject_MockObject $registryMock */
        $registryMock = $this->getMock('Magento\Framework\Registry', [], [], '', false);
        $registryMock->expects($this->once())
            ->method('registry')
            ->with('use_page_cache_plugin')
            ->willReturn($usePageCache);
        $plugin = new HttpPlugin($registryMock);
        $plugin->beforeSendResponse($responseMock);
    }

    public function beforeSendResponseDataProvider()
    {
        return [
            [true, 1],
            [false, 0]
        ];
    }
}
