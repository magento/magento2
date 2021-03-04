<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Store\Test\Unit\Model;

use Magento\Framework\App\Config;
use Magento\Framework\App\Request\Http;
use Magento\Store\Model\BaseUrlChecker;

/**
 * Class BaseUrlCheckerTest covers Magento\Store\Model\BaseUrlChecker.
 */
class BaseUrlCheckerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Holder for BaseUrlChecker instance.
     *
     * @var BaseUrlChecker
     */
    private $baseUrlChecker;

    /**
     * Holder for Config mock.
     *
     * @var Config|\PHPUnit\Framework\MockObject\MockObject
     */
    private $scopeConfig;

    /**
     * Prepare subject for tests.
     */
    protected function setUp(): void
    {
        $this->scopeConfig = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->baseUrlChecker = new BaseUrlChecker(
            $this->scopeConfig
        );
        parent::setUp();
    }

    /**
     * @covers \Magento\Store\Model\BaseUrlChecker::execute()
     */
    public function testExecute()
    {
        $scheme = 'testScheme';
        $host = 'testHost';
        $requestUri = 'testRequestUri';
        /** @var Http|\PHPUnit\Framework\MockObject\MockObject $request */
        $request = $this->getMockBuilder(Http::class)
            ->disableOriginalConstructor()
            ->getMock();
        $request->expects($this->exactly(2))
            ->method('getRequestUri')
            ->willReturn($requestUri);
        $request->expects($this->once())
            ->method('getScheme')
            ->willReturn($scheme);
        $request->expects($this->once())
            ->method('getHttpHost')
            ->willReturn($host);
        $uri = [
            'scheme' => $scheme,
            'host' => $host,
            'path' => $requestUri,
        ];
        $this->assertTrue($this->baseUrlChecker->execute($uri, $request));
    }

    /**
     * @covers \Magento\Store\Model\BaseUrlChecker::isEnabled()
     */
    public function testIsEnabled()
    {
        $this->scopeConfig->expects($this->once())
            ->method('isSetFlag')
            ->with('web/url/redirect_to_base', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)
            ->willReturn(!!1);
        $this->assertTrue($this->baseUrlChecker->isEnabled());
    }

    /**
     * @covers \Magento\Store\Model\BaseUrlChecker::isFrontendSecure()
     */
    public function testIsFrontendSecure()
    {
        $this->scopeConfig->expects($this->once())
            ->method('getValue')
            ->with('web/unsecure/base_url', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)
            ->willReturn('https://localhost');

        $this->scopeConfig->expects($this->once())
            ->method('isSetFlag')
            ->with('web/secure/use_in_frontend', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)
            ->willReturn(!!1);

        $this->assertTrue($this->baseUrlChecker->isFrontendSecure());
    }
}
