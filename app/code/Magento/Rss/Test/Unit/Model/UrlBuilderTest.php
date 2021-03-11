<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Rss\Test\Unit\Model;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * Class UrlBuilderTest
 * @package Magento\Rss\Model
 */
class UrlBuilderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Rss\Model\UrlBuilder
     */
    protected $urlBuilder;

    /**
     * @var \Magento\Framework\UrlInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $urlInterface;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $scopeConfigInterface;

    protected function setUp(): void
    {
        $this->urlInterface = $this->createMock(\Magento\Framework\UrlInterface::class);
        $this->scopeConfigInterface = $this->createMock(\Magento\Framework\App\Config\ScopeConfigInterface::class);

        $objectManagerHelper = new ObjectManagerHelper($this);
        $this->urlBuilder = $objectManagerHelper->getObject(
            \Magento\Rss\Model\UrlBuilder::class,
            [
                'urlBuilder' => $this->urlInterface,
                'scopeConfig' => $this->scopeConfigInterface
            ]
        );
    }

    public function testGetUrlEmpty()
    {
        $this->scopeConfigInterface->expects($this->once())->method('getValue')
            ->with('rss/config/active', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)
            ->willReturn(false);
        $this->assertEquals('', $this->urlBuilder->getUrl());
    }

    public function testGetUrl()
    {
        $this->scopeConfigInterface->expects($this->once())->method('getValue')
            ->with('rss/config/active', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)
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
