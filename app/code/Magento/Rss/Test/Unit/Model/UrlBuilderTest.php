<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Rss\Test\Unit\Model;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * Class UrlBuilderTest
 * @package Magento\Rss\Model
 */
class UrlBuilderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Rss\Model\UrlBuilder
     */
    protected $urlBuilder;

    /**
     * @var \Magento\Framework\UrlInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $urlInterface;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $scopeConfigInterface;

    protected function setUp()
    {
        $this->urlInterface = $this->getMock('Magento\Framework\UrlInterface');
        $this->scopeConfigInterface = $this->getMock('Magento\Framework\App\Config\ScopeConfigInterface');

        $objectManagerHelper = new ObjectManagerHelper($this);
        $this->urlBuilder = $objectManagerHelper->getObject(
            'Magento\Rss\Model\UrlBuilder',
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
            ->will($this->returnValue(false));
        $this->assertEquals('', $this->urlBuilder->getUrl());
    }

    public function testGetUrl()
    {
        $this->scopeConfigInterface->expects($this->once())->method('getValue')
            ->with('rss/config/active', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)
            ->will($this->returnValue(true));
        $this->urlInterface->expects($this->once())->method('getUrl')
            ->with('rss/feed/index', ['type' => 'rss_feed'])
            ->will($this->returnValue('http://magento.com/rss/feed/index/type/rss_feed'));
        $this->assertEquals(
            'http://magento.com/rss/feed/index/type/rss_feed',
            $this->urlBuilder->getUrl(['type' => 'rss_feed']));
    }
}
