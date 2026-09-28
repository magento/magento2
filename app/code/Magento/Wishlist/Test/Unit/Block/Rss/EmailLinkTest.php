<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);


namespace Magento\Wishlist\Test\Unit\Block\Rss;

use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\App\Rss\UrlBuilderInterface;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Framework\Url\EncoderInterface;
use Magento\Wishlist\Block\Rss\EmailLink;
use Magento\Wishlist\Helper\Data;
use Magento\Wishlist\Model\Wishlist;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class EmailLinkTest extends TestCase
{
    use MockCreationTrait;

    /** @var EmailLink */
    protected $link;

    /** @var ObjectManagerHelper */
    protected $objectManagerHelper;

    /** @var Data|MockObject */
    protected $wishlistHelper;

    /** @var UrlBuilderInterface|MockObject */
    protected $urlBuilder;

    /**
     * @var EncoderInterface|MockObject
     */
    protected $urlEncoder;

    protected function setUp(): void
    {
        $wishlist = $this->createPartialMockWithReflection(
            Wishlist::class,
            ['getId', 'getSharingCode']
        );
        $wishlist->expects($this->any())->method('getId')->willReturn(5);
        $wishlist->expects($this->any())->method('getSharingCode')->willReturn('somesharingcode');
        $customer = $this->createMock(CustomerInterface::class);
        $customer->expects($this->any())->method('getId')->willReturn(8);
        $customer->expects($this->any())->method('getEmail')->willReturn('test@example.com');

        $this->wishlistHelper = $this->createPartialMockWithReflection(
            Data::class,
            ['getWishlist', 'getCustomer', 'urlEncode']
        );
        $this->urlEncoder = $this->createPartialMock(EncoderInterface::class, ['encode']);

        $this->wishlistHelper->expects($this->any())->method('getWishlist')->willReturn($wishlist);
        $this->wishlistHelper->expects($this->any())->method('getCustomer')->willReturn($customer);
        $this->urlEncoder->expects($this->any())
            ->method('encode')
            ->willReturnCallback(function ($url) {
                return strtr(base64_encode($url), '+/=', '-_,');
            });

        $this->urlBuilder = $this->createMock(UrlBuilderInterface::class);
        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->link = $this->objectManagerHelper->getObject(
            EmailLink::class,
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
            ->with([
                'type' => 'wishlist',
                'data' => 'OCx0ZXN0QGV4YW1wbGUuY29t',
                '_secure' => false,
                'wishlist_id' => 5,
                'sharing_code' => 'somesharingcode',
            ])
            ->willReturn('http://url.com/rss/feed/index/type/wishlist/wishlist_id/5');
        $this->assertEquals('http://url.com/rss/feed/index/type/wishlist/wishlist_id/5', $this->link->getLink());
    }
}
