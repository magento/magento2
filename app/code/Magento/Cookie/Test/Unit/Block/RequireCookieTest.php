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

/**
 * Class \Magento\Cookie\Test\Unit\Block\RequireCookieTest
 */
class RequireCookieTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|RequireCookie
     */
    private $block;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|Context
     */
    private $context;

    /**
     * Setup Environment
     */
    protected function setUp(): void
    {
        $this->scopeConfig = $this->getMockBuilder(ScopeConfigInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getValue'])
            ->getMockForAbstractClass();
        $this->context = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->context->expects($this->any())->method('getScopeConfig')
            ->willReturn($this->scopeConfig);
        $this->block = $this->getMockBuilder(RequireCookie::class)
            ->setMethods(['escapeHtml', 'escapeUrl', 'getUrl', 'getTriggers'])
            ->setConstructorArgs(
                [
                    'context' => $this->context
                ]
            )->getMock();
    }

    /**
     * Test getScriptOptions() when the settings "Redirect to CMS-page if Cookies are Disabled" is "Yes"
     */
    public function testGetScriptOptionsWhenRedirectToCmsIsYes()
    {
        $this->scopeConfig->expects($this->any())->method('getValue')
            ->with('web/browser_capabilities/cookies')
            ->willReturn('1');

        $this->block->expects($this->any())->method('getUrl')
            ->with('cookie/index/noCookies/')
            ->willReturn('http://magento.com/cookie/index/noCookies/');
        $this->block->expects($this->any())->method('getTriggers')
            ->willReturn('test');
        $this->block->expects($this->any())->method('escapeUrl')
            ->with('http://magento.com/cookie/index/noCookies/')
            ->willReturn('http://magento.com/cookie/index/noCookies/');
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
    public function testGetScriptOptionsWhenRedirectToCmsIsNo()
    {
        $this->scopeConfig->expects($this->any())->method('getValue')
            ->with('web/browser_capabilities/cookies')
            ->willReturn('0');

        $this->block->expects($this->any())->method('getUrl')
            ->with('cookie/index/noCookies/')
            ->willReturn('http://magento.com/cookie/index/noCookies/');
        $this->block->expects($this->any())->method('getTriggers')
            ->willReturn('test');
        $this->block->expects($this->any())->method('escapeUrl')
            ->with('http://magento.com/cookie/index/noCookies/')
            ->willReturn('http://magento.com/cookie/index/noCookies/');
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
