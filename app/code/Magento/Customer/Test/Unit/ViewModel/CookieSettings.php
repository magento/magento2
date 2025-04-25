<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Test\Unit\ViewModel;

use PHPUnit\Framework\TestCase;

class CookieSettings extends TestCase
{
    /**
     * @var \Magento\Customer\ViewModel\CookieSettings
     */
    private $cookieSettings;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $scopeConfigMock;

    protected function setUp(): void
    {
        $this->scopeConfigMock = $this->getMockBuilder(\Magento\Framework\App\Config\ScopeConfigInterface::class)
            ->getMockForAbstractClass();

        $this->cookieSettings = new \Magento\Customer\ViewModel\CookieSettings(
            $this->scopeConfigMock
        );
    }

    public function testGetCookieDomain()
    {
        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with(
                \Magento\Customer\ViewModel\CookieSettings::XML_PATH_COOKIE_DOMAIN,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            )
            ->willReturn('example.com');

        $this->assertEquals('example.com', $this->cookieSettings->getCookieDomain());
    }
}
