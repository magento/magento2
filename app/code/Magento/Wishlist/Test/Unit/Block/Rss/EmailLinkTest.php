<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Wishlist\Test\Unit\Block\Rss;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

class EmailLinkTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Wishlist\Block\Rss\EmailLink */
    protected $link;

    /** @var ObjectManagerHelper */
    protected $objectManagerHelper;

    /** @var \Magento\Wishlist\Helper\Data|\PHPUnit_Framework_MockObject_MockObject */
    protected $wishlistHelper;

    /** @var \Magento\Framework\App\Rss\UrlBuilderInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $urlBuilder;

    /**
     * @var \Magento\Framework\Url\EncoderInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $urlEncoder;

    protected function setUp()
    {
        $wishlist = $this->getMock(\Magento\Wishlist\Model\Wishlist::class, ['getId', 'getSharingCode'], [], '', false);
        $wishlist->expects($this->any())->method('getId')->will($this->returnValue(5));
        $wishlist->expects($this->any())->method('getSharingCode')->will($this->returnValue('somesharingcode'));
        $customer = $this->getMock(\Magento\Customer\Api\Data\CustomerInterface::class, [], [], '', false);
        $customer->expects($this->any())->method('getId')->will($this->returnValue(8));
        $customer->expects($this->any())->method('getEmail')->will($this->returnValue('test@example.com'));

        $this->wishlistHelper = $this->getMock(
            \Magento\Wishlist\Helper\Data::class,
            ['getWishlist', 'getCustomer', 'urlEncode'],
            [],
            '',
            false
        );
        $this->urlEncoder = $this->getMock(\Magento\Framework\Url\EncoderInterface::class, ['encode'], [], '', false);

        $this->wishlistHelper->expects($this->any())->method('getWishlist')->will($this->returnValue($wishlist));
        $this->wishlistHelper->expects($this->any())->method('getCustomer')->will($this->returnValue($customer));
        $this->urlEncoder->expects($this->any())
            ->method('encode')
            ->willReturnCallback(function ($url) {
                return strtr(base64_encode($url), '+/=', '-_,');
            });

        $this->urlBuilder = $this->getMock(\Magento\Framework\App\Rss\UrlBuilderInterface::class);
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
            ->will($this->returnValue('http://url.com/rss/feed/index/type/wishlist/wishlist_id/5'));
        $this->assertEquals('http://url.com/rss/feed/index/type/wishlist/wishlist_id/5', $this->link->getLink());
    }
}
