<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ReleaseNotification\Test\Unit\Model\Connector\ResponseHandler;

use Magento\ReleaseNotification\Model\Connector\ResponseHandler\NotificationResponse;

/**
 * Class NotificationResponseTest
 */
class NotificationResponseTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @param $expected
     * @param $responseData
     * @dataProvider handleResultDataProvider
     */
    public function testHandleResult($expected, $responseData)
    {
        $handler = new NotificationResponse();
        $actualResponse = $handler->handleResponse($responseData);
        $this->assertEquals($expected, $actualResponse['version']);
    }

    public function handleResultDataProvider()
    {
        return [
            [false, []],
            ['2.2.2', ['version' => '2.2.2']]
        ];
    }
}
