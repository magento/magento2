<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\PageCache\Test\Unit\Model\App\Response;

use Magento\PageCache\Model\App\Response\HttpPlugin;

class HttpPluginTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param \Magento\Framework\App\Response\FileInterface $responseInstanceClass
     * @param int $sendVaryCalled
     *
     * @dataProvider beforeSendResponseDataProvider
     */
    public function testBeforeSendResponse($responseInstanceClass, $sendVaryCalled)
    {
        /** @var \Magento\Framework\App\Response\Http | \PHPUnit_Framework_MockObject_MockObject $responseMock */
        $responseMock = $this->getMock($responseInstanceClass, [], [], '', false);
        $responseMock->expects($this->exactly($sendVaryCalled))
            ->method('sendVary');
        $plugin = new HttpPlugin();
        $plugin->beforeSendResponse($responseMock);
    }

    /**
     * @return array
     */
    public function beforeSendResponseDataProvider()
    {
        return [
            ['Magento\Framework\App\Response\Http', 1],
            ['Magento\MediaStorage\Model\File\Storage\Response', 0]
        ];
    }
}
