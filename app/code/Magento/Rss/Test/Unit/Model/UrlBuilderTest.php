<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Rss\Test\Unit\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Framework\UrlInterface;
use Magento\Rss\Model\UrlBuilder;
use Magento\Store\Model\ScopeInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class UrlBuilderTest extends TestCase
{
    /**
     * @var UrlBuilder
     */
    protected $urlBuilder;

    /**
     * @var UrlInterface|MockObject
     */
    protected $urlInterface;

    /**
     * @var ScopeConfigInterface|MockObject
     */
    protected $scopeConfigInterface;

    protected function setUp(): void
    {
        $this->urlInterface = $this->getMockForAbstractClass(UrlInterface::class);
        $this->scopeConfigInterface = $this->getMockForAbstractClass(ScopeConfigInterface::class);

        $objectManagerHelper = new ObjectManagerHelper($this);
        $this->urlBuilder = $objectManagerHelper->getObject(
            UrlBuilder::class,
            [
                'urlBuilder' => $this->urlInterface,
                'scopeConfig' => $this->scopeConfigInterface
            ]
        );
    }

    public function testGetUrlEmpty()
    {
        $this->scopeConfigInterface->expects($this->once())->method('getValue')
            ->with('rss/config/active', ScopeInterface::SCOPE_STORE)
            ->willReturn(false);
        $this->assertEquals('', $this->urlBuilder->getUrl());
    }

    public function testGetUrl()
    {
        $this->scopeConfigInterface->expects($this->once())->method('getValue')
            ->with('rss/config/active', ScopeInterface::SCOPE_STORE)
            ->willReturn(true);
        $this->urlInterface->expects($this->once())->method('getUrl')
            ->with('rss/feed/index', ['type' => 'rss_feed'])
            ->willReturn('http://magento.com/rss/feed/index/type/rss_feed');
        $this->assertEquals(
            'http://magento.com/rss/feed/index/type/rss_feed',
            $this->urlBuilder->getUrl(['type' => 'rss_feed'])
        );
    }
}
