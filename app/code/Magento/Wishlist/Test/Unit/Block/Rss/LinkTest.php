<?php declare(strict_types=1);
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Wishlist\Test\Unit\Block\Rss;

use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Rss\UrlBuilderInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Framework\Url\EncoderInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Wishlist\Block\Rss\Link;
use Magento\Wishlist\Helper\Data;
use Magento\Wishlist\Model\Wishlist;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class LinkTest extends TestCase
{
    /** @var Link */
    protected $link;

    /** @var ObjectManagerHelper */
    protected $objectManagerHelper;

    /** @var Data|MockObject */
    protected $wishlistHelper;

    /** @var UrlBuilderInterface|MockObject */
    protected $urlBuilder;

    /** @var ScopeConfigInterface|MockObject */
    protected $scopeConfig;

    /**
     * @var EncoderInterface|MockObject
     */
    protected $urlEncoder;

    protected function setUp(): void
    {
        $wishlist = $this->createPartialMock(Wishlist::class, ['getId']);
        $wishlist->expects($this->any())->method('getId')->will($this->returnValue(5));

        $customer = $this->createMock(CustomerInterface::class);
        $customer->expects($this->any())->method('getId')->will($this->returnValue(8));
        $customer->expects($this->any())->method('getEmail')->will($this->returnValue('test@example.com'));

        $this->wishlistHelper = $this->createPartialMock(
            Data::class,
            ['getWishlist', 'getCustomer', 'urlEncode']
        );
        $this->urlEncoder = $this->createPartialMock(EncoderInterface::class, ['encode']);

        $this->wishlistHelper->expects($this->any())->method('getWishlist')->will($this->returnValue($wishlist));
        $this->wishlistHelper->expects($this->any())->method('getCustomer')->will($this->returnValue($customer));
        $this->urlEncoder->expects($this->any())
            ->method('encode')
            ->willReturnCallback(function ($url) {
                return strtr(base64_encode($url), '+/=', '-_,');
            });

        $this->urlBuilder = $this->createMock(UrlBuilderInterface::class);
        $this->scopeConfig = $this->createMock(ScopeConfigInterface::class);

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->link = $this->objectManagerHelper->getObject(
            Link::class,
            [
                'wishlistHelper' => $this->wishlistHelper,
                'rssUrlBuilder' => $this->urlBuilder,
                'scopeConfig' => $this->scopeConfig,
                'urlEncoder' => $this->urlEncoder,
            ]
        );
    }

    public function testGetLink()
    {
        $this->urlBuilder->expects($this->atLeastOnce())->method('getUrl')
            ->with($this->equalTo([
                'type' => 'wishlist',
                'data' => 'OCx0ZXN0QGV4YW1wbGUuY29t',
                '_secure' => false,
                'wishlist_id' => 5,
            ]))
            ->will($this->returnValue('http://url.com/rss/feed/index/type/wishlist/wishlist_id/5'));
        $this->assertEquals('http://url.com/rss/feed/index/type/wishlist/wishlist_id/5', $this->link->getLink());
    }

    public function testIsRssAllowed()
    {
        $this->scopeConfig
            ->expects($this->atLeastOnce())
            ->method('isSetFlag')
            ->with('rss/wishlist/active', ScopeInterface::SCOPE_STORE)
            ->will($this->returnValue(true));
        $this->assertEquals(true, $this->link->isRssAllowed());
    }
}
