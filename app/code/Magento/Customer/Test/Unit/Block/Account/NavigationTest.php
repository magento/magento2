<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Test\Unit\Block\Account;

use PHPUnit\Framework\TestCase;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Customer\Block\Account\Navigation;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\View\LayoutInterface;
use Magento\Wishlist\Block\Link as WishListLink;
use Magento\Customer\Block\Account\Link as CustomerAccountLink;

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
     * @var Context|\PHPUnit_Framework_MockObject_MockObject
     */
    private $contextMock;

    /**
     * @var LayoutInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $layoutMock;

    /**
     * Setup environment for test
     */
    protected function setUp()
    {
        $this->contextMock = $this->createMock(Context::class);
        $this->layoutMock = $this->createMock(LayoutInterface::class);
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
        $wishListLink = $this->getMockBuilder(WishListLink::class)
            ->disableOriginalConstructor()
            ->setMethods(['getSortOrder'])
            ->getMock();

        $customerAccountLink = $this->getMockBuilder(CustomerAccountLink::class)
            ->disableOriginalConstructor()
            ->setMethods(['getSortOrder'])
            ->getMock();

        $wishListLink->expects($this->any())
            ->method('getSortOrder')
            ->willReturn(100);

        $customerAccountLink->expects($this->any())
            ->method('getSortOrder')
            ->willReturn(20);

        $nameInLayout = 'top.links';

        $blockChildren = [
            'wishListLink' => $wishListLink,
            'customerAccountLink' => $customerAccountLink
        ];

        $this->navigation->setNameInLayout($nameInLayout);
        $this->layoutMock->expects($this->any())
            ->method('getChildBlocks')
            ->with($nameInLayout)
            ->willReturn($blockChildren);

        /* Assertion */
        $this->assertEquals(
            [
                0 => $wishListLink,
                1 => $customerAccountLink
            ],
            $this->navigation->getLinks()
        );
    }
}
