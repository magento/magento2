<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CmsUrlRewriteGraphQl\Test\Unit\Model\Resolver\UrlRewrite;

use Magento\Cms\Helper\Page;
use Magento\CmsUrlRewriteGraphQl\Model\Resolver\UrlRewrite\HomePageUrlLocator;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use PHPUnit\Framework\TestCase;

/**
 * @see HomePageUrlLocator
 */
class HomePageUrlLocatorTest extends TestCase
{
    /**
     * @var HomePageUrlLocator
     */
    private HomePageUrlLocator $homePageUrlLocator;

    /**
     * @var ScopeConfigInterface
     */
    private ScopeConfigInterface $scopeConfigInterfaceMock;

    /**
     * @var string
     */
    private string $homePageUrlKey = '/';

    protected function setUp(): void
    {
        $this->scopeConfigInterfaceMock = $this->createMock(ScopeConfigInterface::class);
        $this->homePageUrlLocator = new HomePageUrlLocator($this->scopeConfigInterfaceMock);
    }

    public function testLocateUrl(): void
    {
        $this->scopeConfigInterfaceMock
            ->expects($this->once())
            ->method('getValue')
            ->with(Page::XML_PATH_HOME_PAGE, ScopeInterface::SCOPE_STORE)
            ->willReturn('home');
        $this->assertEquals('home', $this->homePageUrlLocator->locateUrl($this->homePageUrlKey));
    }

    public function testLocateUrlWhenMultipleStoresHaveSameHomePageUrl(): void
    {
        $this->scopeConfigInterfaceMock
            ->expects($this->once())
            ->method('getValue')
            ->with(Page::XML_PATH_HOME_PAGE, ScopeInterface::SCOPE_STORE)
            ->willReturn('home|8');
        $this->assertEquals('home', $this->homePageUrlLocator->locateUrl($this->homePageUrlKey));
    }
}
