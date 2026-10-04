<?php
/**
 * Copyright 2023 Adobe
 * All Rights Reserved.
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
        $this->scopeConfigMock = $this->createMock(\Magento\Framework\App\Config\ScopeConfigInterface::class);

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
