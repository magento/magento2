<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Test\Unit\Block\Account;

use Magento\Customer\Block\Account\Link as CustomerAccountLink;
use Magento\Customer\Block\Account\Navigation;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\View\LayoutInterface;
use Magento\Wishlist\Block\Link as WishListLink;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class NavigationTest extends TestCase
{
    /**
     * @var ObjectManagerHelper
     */
    private $objectManagerHelper;

    /**
     * @var Navigation
     */
    private $navigation;

    /**
     * @var Context|MockObject
     */
    private $contextMock;

    /**
     * @var LayoutInterface|MockObject
     */
    private $layoutMock;

    /**
     * Setup environment for test
     */
    protected function setUp(): void
    {
        $this->contextMock = $this->createMock(Context::class);
        $this->layoutMock = $this->getMockForAbstractClass(LayoutInterface::class);
        $this->contextMock->expects($this->any())
            ->method('getLayout')
            ->willReturn($this->layoutMock);
        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->navigation = $this->objectManagerHelper->getObject(
            Navigation::class,
            [
                'context' => $this->contextMock
            ]
        );
    }

    /**
     * Test get links with block customer account link and wish list link
     *
     * @return void
     */
    public function testGetLinksWithCustomerAndWishList()
    {
        $wishListLinkMock = $this->getMockBuilder(WishListLink::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getSortOrder'])
            ->getMock();

        $customerAccountLinkMock = $this->getMockBuilder(CustomerAccountLink::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getSortOrder'])
            ->getMock();

        $wishListLinkMock->expects($this->any())
            ->method('getSortOrder')
            ->willReturn(100);

        $customerAccountLinkMock->expects($this->any())
            ->method('getSortOrder')
            ->willReturn(20);

        $nameInLayout = 'top.links';

        $blockChildren = [
            'wishListLink' => $wishListLinkMock,
            'customerAccountLink' => $customerAccountLinkMock
        ];

        $this->navigation->setNameInLayout($nameInLayout);
        $this->layoutMock->expects($this->any())
            ->method('getChildBlocks')
            ->with($nameInLayout)
            ->willReturn($blockChildren);

        /* Assertion */
        $this->assertEquals(
            [
                0 => $wishListLinkMock,
                1 => $customerAccountLinkMock
            ],
            $this->navigation->getLinks()
        );
    }
}
