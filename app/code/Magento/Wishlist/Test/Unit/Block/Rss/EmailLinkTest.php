<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Wishlist\Test\Unit\Block\Rss;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

class EmailLinkTest extends \PHPUnit\Framework\TestCase
{
    /** @var \Magento\Wishlist\Block\Rss\EmailLink */
    protected $link;

    /** @var ObjectManagerHelper */
    protected $objectManagerHelper;

    /** @var \Magento\Wishlist\Helper\Data|\PHPUnit\Framework\MockObject\MockObject */
    protected $wishlistHelper;

    /** @var \Magento\Framework\App\Rss\UrlBuilderInterface|\PHPUnit\Framework\MockObject\MockObject */
    protected $urlBuilder;

    /**
     * @var \Magento\Framework\Url\EncoderInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $urlEncoder;

    protected function setUp(): void
    {
        $wishlist = $this->createPartialMock(\Magento\Wishlist\Model\Wishlist::class, ['getId', 'getSharingCode']);
        $wishlist->expects($this->any())->method('getId')->willReturn(5);
        $wishlist->expects($this->any())->method('getSharingCode')->willReturn('somesharingcode');
        $customer = $this->createMock(\Magento\Customer\Api\Data\CustomerInterface::class);
        $customer->expects($this->any())->method('getId')->willReturn(8);
        $customer->expects($this->any())->method('getEmail')->willReturn('test@example.com');

        $this->wishlistHelper = $this->createPartialMock(
            \Magento\Wishlist\Helper\Data::class,
            ['getWishlist', 'getCustomer', 'urlEncode']
        );
        $this->urlEncoder = $this->createPartialMock(\Magento\Framework\Url\EncoderInterface::class, ['encode']);

        $this->wishlistHelper->expects($this->any())->method('getWishlist')->willReturn($wishlist);
        $this->wishlistHelper->expects($this->any())->method('getCustomer')->willReturn($customer);
        $this->urlEncoder->expects($this->any())
            ->method('encode')
            ->willReturnCallback(function ($url) {
                return strtr(base64_encode($url), '+/=', '-_,');
            });

        $this->urlBuilder = $this->createMock(\Magento\Framework\App\Rss\UrlBuilderInterface::class);
        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->link = $this->objectManagerHelper->getObject(
            \Magento\Wishlist\Block\Rss\EmailLink::class,
            [
                'wishlistHelper' => $this->wishlistHelper,
                'rssUrlBuilder' => $this->urlBuilder,
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
                'sharing_code' => 'somesharingcode',
            ]))
            ->willReturn('http://url.com/rss/feed/index/type/wishlist/wishlist_id/5');
        $this->assertEquals('http://url.com/rss/feed/index/type/wishlist/wishlist_id/5', $this->link->getLink());
    }
}
