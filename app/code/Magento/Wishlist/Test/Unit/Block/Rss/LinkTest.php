<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);


namespace Magento\Wishlist\Test\Unit\Block\Rss;

use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Rss\UrlBuilderInterface;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\Url\EncoderInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Wishlist\Block\Rss\Link;
use Magento\Wishlist\Helper\Data;
use Magento\Wishlist\Model\Wishlist;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class LinkTest extends TestCase
{
    /**
     * @var Link
     */
    protected $link;

    /**
     * @var Context|MockObject
     */
    protected $context;

    /**
     * @var Data|MockObject
     */
    protected $wishlistHelper;

    /**
     * @var UrlBuilderInterface|MockObject
     */
    protected $urlBuilder;

    /**
     * @var ScopeConfigInterface|MockObject
     */
    protected $scopeConfig;

    /**
     * @var EncoderInterface|MockObject
     */
    protected $urlEncoder;

    protected function setUp(): void
    {
        $wishlist = $this->createPartialMock(Wishlist::class, ['getId']);
        $wishlist->expects($this->any())->method('getId')->willReturn(5);

        $customer = $this->createStub(CustomerInterface::class);
        $customer->method('getId')->willReturn(8);
        $customer->method('getEmail')->willReturn('test@example.com');

        $this->wishlistHelper = $this->createPartialMock(Data::class, ['getWishlist', 'getCustomer']);
        $this->urlEncoder = $this->createPartialMock(EncoderInterface::class, ['encode']);

        $this->wishlistHelper->expects($this->any())->method('getWishlist')->willReturn($wishlist);
        $this->wishlistHelper->expects($this->any())->method('getCustomer')->willReturn($customer);
        $this->urlEncoder->expects($this->any())
            ->method('encode')
            ->willReturnCallback(
                function ($url) {
                    return strtr(base64_encode($url), '+/=', '-_,');
                }
            );

        $this->urlBuilder = $this->createMock(UrlBuilderInterface::class);
        $this->scopeConfig = $this->createMock(ScopeConfigInterface::class);
        $this->context = $this->createMock(Context::class);

        $this->context->method('getScopeConfig')->willReturn($this->scopeConfig);

        $this->link = new Link(
            $this->context,
            $this->wishlistHelper,
            $this->urlBuilder,
            $this->urlEncoder
        );
    }

    public function testGetLink()
    {
        $this->urlBuilder->expects($this->atLeastOnce())->method('getUrl')
            ->with(
                [
                'type' => 'wishlist',
                'data' => 'OCx0ZXN0QGV4YW1wbGUuY29t',
                '_secure' => false,
                'wishlist_id' => 5,
                ]
            )
            ->willReturn('http://url.com/rss/feed/index/type/wishlist/wishlist_id/5');
        $this->assertEquals('http://url.com/rss/feed/index/type/wishlist/wishlist_id/5', $this->link->getLink());
    }

    public function testIsRssAllowed()
    {
        $this->scopeConfig
            ->expects($this->atLeastOnce())
            ->method('isSetFlag')
            ->with('rss/wishlist/active', ScopeInterface::SCOPE_STORE)
            ->willReturn(true);
        $this->assertTrue($this->link->isRssAllowed());
    }
}
