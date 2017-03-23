<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\App\Test\Unit\Response\HeaderProvider;

use Magento\Framework\App\Response\HeaderProvider\XssProtection;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class XssProtectionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider userAgentDataProvider
     * @param string $userAgent
     * @param string $expectedHeader
     */
    public function testGetValue($userAgent, $expectedHeader)
    {
        $headerServiceMock = $this->getMockBuilder(\Magento\Framework\HTTP\Header::class)
            ->disableOriginalConstructor()
            ->getMock();
        $headerServiceMock->expects($this->once())->method('getHttpUserAgent')->willReturn($userAgent);
        $model = (new ObjectManager($this))->getObject(
            \Magento\Framework\App\Response\HeaderProvider\XssProtection::class,
            ['headerService' => $headerServiceMock]
        );
        $this->assertSame($expectedHeader, $model->getValue());
    }

    /**
     * @return array
     */
    public function userAgentDataProvider()
    {
        return [
            [
                'user-agent' => 'Mozilla/5.0 (compatible; MSIE 8.0; Windows NT 6.1; Trident/4.0; GTB7.4)',
                'expected-header' => XssProtection::HEADER_DISABLED
            ],
            [
                'user-agent' => 'Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 6.1; Trident/4.0; GTB7.4)',
                'expected-header' => XssProtection::HEADER_ENABLED
            ],
            [
                'user-agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_10_1) Chrome/41.0.2227.1 Safari/537.36',
                'expected-header' => XssProtection::HEADER_ENABLED
            ],
        ];
    }
}
