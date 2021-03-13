<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Cookie\Test\Unit\Block;

use Magento\Cookie\Block\RequireCookie;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Escaper;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\Template\Context;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Magento\Cookie\Test\Unit\Block\RequireCookieTest
 */
class RequireCookieTest extends TestCase
{
    private const STUB_NOCOOKIES_URL = 'http://magento.com/cookie/index/noCookies/';

    /**
     * @var MockObject|RequireCookie
     */
    private $block;

    /**
     * @var ScopeConfigInterface|MockObject
     */
    private $scopeConfigMock;

    /**
     * @var Escaper|MockObject
     */
    private $escaperMock;

    /**
     * @var UrlInterface|MockObject
     */
    private $urlBuilderMock;

    /**
     * Setup Environment
     */
    protected function setUp(): void
    {
        $this->scopeConfigMock = $this->createMock(ScopeConfigInterface::class);
        $this->escaperMock = $this->createMock(Escaper::class);
        $this->urlBuilderMock = $this->createMock(UrlInterface::class);

        $contextMock = $this->createMock(Context::class);
        $contextMock->method('getScopeConfig')->willReturn($this->scopeConfigMock);
        $contextMock->method('getEscaper')->willReturn($this->escaperMock);
        $contextMock->method('getUrlBuilder')->willReturn($this->urlBuilderMock);

        $this->block = new RequireCookie($contextMock);
        $this->block->setData('triggers', 'test');
    }

    /**
     * Test getScriptOptions() when the settings "Redirect to CMS-page if Cookies are Disabled" is "Yes"
     */
    public function testGetScriptOptionsWhenRedirectToCmsIsYes(): void
    {
        $this->setupMocks(1);

        $this->assertEquals(
            '{"noCookieUrl":"http:\/\/magento.com\/cookie\/index\/noCookies\/",' .
            '"triggers":"test","isRedirectCmsPage":true}',
            $this->block->getScriptOptions()
        );
    }

    /**
     * Test getScriptOptions() when the settings "Redirect to CMS-page if Cookies are Disabled" is "No"
     */
    public function testGetScriptOptionsWhenRedirectToCmsIsNo(): void
    {
        $this->setupMocks(0);

        $this->assertEquals(
            '{"noCookieUrl":"http:\/\/magento.com\/cookie\/index\/noCookies\/",' .
            '"triggers":"test","isRedirectCmsPage":false}',
            $this->block->getScriptOptions()
        );
    }

    /**
     * @param int $isEnabled
     * @return void
     */
    private function setupMocks(int $isEnabled): void
    {
        $this->scopeConfigMock->method('getValue')->with('web/browser_capabilities/cookies')->willReturn($isEnabled);
        $this->escaperMock->method('escapeHtml')->with('test')->willReturn('test');
        $this->escaperMock->method('escapeUrl')->with(self::STUB_NOCOOKIES_URL)->willReturn(self::STUB_NOCOOKIES_URL);
        $this->urlBuilderMock->method('getUrl')->with('cookie/index/noCookies/', [])->willReturn(self::STUB_NOCOOKIES_URL);
    }
}
