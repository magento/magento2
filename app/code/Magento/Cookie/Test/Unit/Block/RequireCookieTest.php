<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Cookie\Test\Unit\Block;

use Magento\Cookie\Block\RequireCookie;
use Magento\Framework\App\Config\ScopeConfigInterface;
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
     * @var MockObject|ScopeConfigInterface
     */
    private $scopeConfigMock;

    /**
     * @var MockObject|Context
     */
    private $contextMock;

    /**
     * Setup Environment
     */
    protected function setUp(): void
    {
        $this->scopeConfigMock = $this->getMockBuilder(ScopeConfigInterface::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getValue'])
            ->getMockForAbstractClass();

        $this->contextMock = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->contextMock->expects($this->any())->method('getScopeConfig')
            ->willReturn($this->scopeConfigMock);

        $this->block = $this->getMockBuilder(RequireCookie::class)
            ->addMethods(['getTriggers'])
            ->onlyMethods(['escapeHtml', 'escapeUrl', 'getUrl'])
            ->setConstructorArgs(
                [
                    'context' => $this->contextMock
                ]
            )->getMock();
    }

    /**
     * Test getScriptOptions() when the settings "Redirect to CMS-page if Cookies are Disabled" is "Yes"
     */
    public function testGetScriptOptionsWhenRedirectToCmsIsYes(): void
    {
        $this->scopeConfigMock->expects($this->any())->method('getValue')
            ->with('web/browser_capabilities/cookies')
            ->willReturn('1');

        $this->block->expects($this->any())->method('getUrl')
            ->with('cookie/index/noCookies/')
            ->willReturn(self::STUB_NOCOOKIES_URL);
        $this->block->expects($this->any())->method('getTriggers')
            ->willReturn('test');
        $this->block->expects($this->any())->method('escapeUrl')
            ->with(self::STUB_NOCOOKIES_URL)
            ->willReturn(self::STUB_NOCOOKIES_URL);
        $this->block->expects($this->any())->method('escapeHtml')
            ->with('test')
            ->willReturn('test');

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
        $this->scopeConfigMock->expects($this->any())->method('getValue')
            ->with('web/browser_capabilities/cookies')
            ->willReturn('0');

        $this->block->expects($this->any())->method('getUrl')
            ->with('cookie/index/noCookies/')
            ->willReturn(self::STUB_NOCOOKIES_URL);
        $this->block->expects($this->any())->method('getTriggers')
            ->willReturn('test');
        $this->block->expects($this->any())->method('escapeUrl')
            ->with(self::STUB_NOCOOKIES_URL)
            ->willReturn(self::STUB_NOCOOKIES_URL);
        $this->block->expects($this->any())->method('escapeHtml')
            ->with('test')
            ->willReturn('test');

        $this->assertEquals(
            '{"noCookieUrl":"http:\/\/magento.com\/cookie\/index\/noCookies\/",' .
            '"triggers":"test","isRedirectCmsPage":false}',
            $this->block->getScriptOptions()
        );
    }
}
