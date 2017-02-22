<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Wishlist\Test\Unit\Model;

use Magento\Wishlist\Model\ResourceModel\Item\Collection;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ItemCarrierTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Wishlist\Model\ItemCarrier */
    protected $model;

    /** @var \Magento\Customer\Model\Session|\PHPUnit_Framework_MockObject_MockObject */
    protected $sessionMock;

    /** @var \Magento\Wishlist\Model\LocaleQuantityProcessor|\PHPUnit_Framework_MockObject_MockObject */
    protected $quantityProcessorMock;

    /** @var \Magento\Checkout\Model\Cart|\PHPUnit_Framework_MockObject_MockObject */
    protected $cartMock;

    /** @var \Psr\Log\LoggerInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $loggerMock;

    /** @var \Magento\Wishlist\Helper\Data|\PHPUnit_Framework_MockObject_MockObject */
    protected $wishlistHelperMock;

    /** @var \Magento\Checkout\Helper\Cart|\PHPUnit_Framework_MockObject_MockObject */
    protected $cartHelperMock;

    /** @var \Magento\Framework\UrlInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $urlBuilderMock;

    /** @var \Magento\Framework\Message\ManagerInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $managerMock;

    /** @var \Magento\Framework\App\Response\RedirectInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $redirectMock;

    protected function setUp()
    {
        $this->sessionMock = $this->getMockBuilder('Magento\Customer\Model\Session')
            ->disableOriginalConstructor()
            ->getMock();
        $this->quantityProcessorMock = $this->getMockBuilder('Magento\Wishlist\Model\LocaleQuantityProcessor')
            ->disableOriginalConstructor()
            ->getMock();
        $this->cartMock = $this->getMockBuilder('Magento\Checkout\Model\Cart')
            ->disableOriginalConstructor()
            ->getMock();
        $this->loggerMock = $this->getMockBuilder('Psr\Log\LoggerInterface')
            ->getMockForAbstractClass();
        $this->wishlistHelperMock = $this->getMockBuilder('Magento\Wishlist\Helper\Data')
            ->disableOriginalConstructor()
            ->getMock();
        $this->cartHelperMock = $this->getMockBuilder('Magento\Checkout\Helper\Cart')
            ->disableOriginalConstructor()
            ->getMock();
        $this->urlBuilderMock = $this->getMockBuilder('Magento\Framework\UrlInterface')
            ->getMockForAbstractClass();
        $this->managerMock = $this->getMockBuilder('Magento\Framework\Message\ManagerInterface')
            ->getMockForAbstractClass();
        $this->redirectMock = $this->getMockBuilder('Magento\Framework\App\Response\RedirectInterface')
            ->getMockForAbstractClass();

        $this->model = new \Magento\Wishlist\Model\ItemCarrier(
            $this->sessionMock,
            $this->quantityProcessorMock,
            $this->cartMock,
            $this->loggerMock,
            $this->wishlistHelperMock,
            $this->cartHelperMock,
            $this->urlBuilderMock,
            $this->managerMock,
            $this->redirectMock
        );
    }

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testMoveAllToCart()
    {
        $wishlistId = 7;
        $sessionCustomerId = 23;
        $itemOneId = 14;
        $itemTwoId = 17;
        $productOneName = 'product one';
        $productTwoName = 'product two';
        $qtys = [14 => 21];
        $isOwner = true;
        $indexUrl = 'index_url';
        $redirectUrl = 'redirect_url';

        /** @var \Magento\Wishlist\Model\Item|\PHPUnit_Framework_MockObject_MockObject $itemOneMock */
        $itemOneMock = $this->getMockBuilder('Magento\Wishlist\Model\Item')
            ->setMethods(['getProduct', 'unsProduct', 'getId', 'setQty', 'addToCart', 'delete', 'getProductUrl'])
            ->disableOriginalConstructor()
            ->getMock();
        /** @var \Magento\Wishlist\Model\Item|\PHPUnit_Framework_MockObject_MockObject $itemTwoMock */
        $itemTwoMock = $this->getMockBuilder('Magento\Wishlist\Model\Item')
            ->setMethods(['getProduct', 'unsProduct', 'getId', 'setQty', 'addToCart', 'delete', 'getProductUrl'])
            ->disableOriginalConstructor()
            ->getMock();

        /** @var \Magento\Catalog\Model\Product|\PHPUnit_Framework_MockObject_MockObject $productOneMock */
        $productOneMock = $this->getMockBuilder('Magento\Catalog\Model\Product')
            ->setMethods(['getDisableAddToCart', 'setDisableAddToCart', 'getName'])
            ->disableOriginalConstructor()
            ->getMock();
        /** @var \Magento\Catalog\Model\Product|\PHPUnit_Framework_MockObject_MockObject $productTwoMock */
        $productTwoMock = $this->getMockBuilder('Magento\Catalog\Model\Product')
            ->setMethods(['getDisableAddToCart', 'setDisableAddToCart', 'getName'])
            ->disableOriginalConstructor()
            ->getMock();

        $itemOneMock->expects($this->any())
            ->method('getProduct')
            ->willReturn($productOneMock);
        $itemTwoMock->expects($this->any())
            ->method('getProduct')
            ->willReturn($productTwoMock);

        $collection = [$itemOneMock, $itemTwoMock];

        /** @var \Magento\Wishlist\Model\Wishlist|\PHPUnit_Framework_MockObject_MockObject $wishlistMock */
        $wishlistMock = $this->getMockBuilder('Magento\Wishlist\Model\Wishlist')
            ->disableOriginalConstructor()
            ->getMock();

        $this->sessionMock->expects($this->once())
            ->method('getCustomerId')
            ->willReturn($sessionCustomerId);

        $wishlistMock->expects($this->once())
            ->method('isOwner')
            ->with($sessionCustomerId)
            ->willReturn($isOwner);
        $wishlistMock->expects($this->once())
            ->method('getId')
            ->willReturn($wishlistId);

        /** @var Collection|\PHPUnit_Framework_MockObject_MockObject $collectionMock */
        $collectionMock = $this->getMockBuilder('Magento\Wishlist\Model\ResourceModel\Item\Collection')
            ->disableOriginalConstructor()
            ->getMock();

        $wishlistMock->expects($this->once())
            ->method('getItemCollection')
            ->willReturn($collectionMock);

        $collectionMock->expects($this->once())
            ->method('setVisibilityFilter')
            ->with(true)
            ->willReturn($collection);

        $productOneMock->expects($this->once())
            ->method('getDisableAddToCart')
            ->willReturn(true);
        $productOneMock->expects($this->once())
            ->method('setDisableAddToCart')
            ->with(true);
        $productTwoMock->expects($this->once())
            ->method('getDisableAddToCart')
            ->willReturn(false);
        $productTwoMock->expects($this->once())
            ->method('setDisableAddToCart')
            ->with(false);

        $itemOneMock->expects($this->once())
            ->method('unsProduct');
        $itemTwoMock->expects($this->once())
            ->method('unsProduct');
        $itemOneMock->expects($this->exactly(2))
            ->method('getId')
            ->willReturn($itemOneId);
        $itemTwoMock->expects($this->once())
            ->method('getId')
            ->willReturn($itemTwoId);

        $this->quantityProcessorMock->expects($this->once())
            ->method('process')
            ->with($qtys[$itemOneId])
            ->willReturnArgument(0);
        $itemOneMock->expects($this->once())
            ->method('setQty')
            ->with($qtys[$itemOneId])
            ->willReturnSelf();
        $itemTwoMock->expects($this->never())
            ->method('setQty');

        $itemOneMock->expects($this->once())
            ->method('addToCart')
            ->with($this->cartMock, $isOwner)
            ->willReturn(false);
        $itemTwoMock->expects($this->once())
            ->method('addToCart')
            ->with($this->cartMock, $isOwner)
            ->willReturn(true);

        $this->wishlistHelperMock->expects($this->once())
            ->method('getListUrl')
            ->with($wishlistId)
            ->willReturn($indexUrl);

        $this->cartHelperMock->expects($this->once())
            ->method('getShouldRedirectToCart')
            ->with(null)
            ->willReturn(true);
        $this->cartHelperMock->expects($this->once())
            ->method('getCartUrl')
            ->willReturn($redirectUrl);

        $wishlistMock->expects($this->once())
            ->method('save')
            ->willReturnSelf();

        $productOneMock->expects($this->any())
            ->method('getName')
            ->willReturn($productOneName);
        $productTwoMock->expects($this->any())
            ->method('getName')
            ->willReturn($productTwoName);

        $this->managerMock->expects($this->once())
            ->method('addSuccess')
            ->with(__('%1 product(s) have been added to shopping cart: %2.', 1, '"' . $productTwoName . '"'), null)
            ->willReturnSelf();

        $this->cartMock->expects($this->once())
            ->method('save')
            ->willReturnSelf();

        /** @var \Magento\Quote\Model\Quote|\PHPUnit_Framework_MockObject_MockObject $collectionMock */
        $quoteMock = $this->getMockBuilder('Magento\Quote\Model\Quote')
            ->disableOriginalConstructor()
            ->getMock();

        $this->cartMock->expects($this->once())
            ->method('getQuote')
            ->willReturn($quoteMock);

        $quoteMock->expects($this->once())
            ->method('collectTotals')
            ->willReturnSelf();

        $this->wishlistHelperMock->expects($this->once())
            ->method('calculate')
            ->willReturnSelf();

        $this->assertEquals($redirectUrl, $this->model->moveAllToCart($wishlistMock, $qtys));
    }

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testMoveAllToCartWithNotSalableAndOptions()
    {
        $sessionCustomerId = 23;
        $itemOneId = 14;
        $itemTwoId = 17;
        $productOneName = 'product one';
        $productTwoName = 'product two';
        $qtys = [14 => 21, 17 => 29];
        $isOwner = false;
        $indexUrl = 'index_url';
        $redirectUrl = 'redirect_url';
        $sharingCode = 'sharingcode';

        /** @var \Magento\Wishlist\Model\Item|\PHPUnit_Framework_MockObject_MockObject $itemOneMock */
        $itemOneMock = $this->getMockBuilder('Magento\Wishlist\Model\Item')
            ->setMethods(['getProduct', 'unsProduct', 'getId', 'setQty', 'addToCart', 'delete', 'getProductUrl'])
            ->disableOriginalConstructor()
            ->getMock();
        /** @var \Magento\Wishlist\Model\Item|\PHPUnit_Framework_MockObject_MockObject $itemTwoMock */
        $itemTwoMock = $this->getMockBuilder('Magento\Wishlist\Model\Item')
            ->setMethods(['getProduct', 'unsProduct', 'getId', 'setQty', 'addToCart', 'delete', 'getProductUrl'])
            ->disableOriginalConstructor()
            ->getMock();

        /** @var \Magento\Catalog\Model\Product|\PHPUnit_Framework_MockObject_MockObject $productOneMock */
        $productOneMock = $this->getMockBuilder('Magento\Catalog\Model\Product')
            ->setMethods(['getDisableAddToCart', 'setDisableAddToCart', 'getName'])
            ->disableOriginalConstructor()
            ->getMock();
        /** @var \Magento\Catalog\Model\Product|\PHPUnit_Framework_MockObject_MockObject $productTwoMock */
        $productTwoMock = $this->getMockBuilder('Magento\Catalog\Model\Product')
            ->setMethods(['getDisableAddToCart', 'setDisableAddToCart', 'getName'])
            ->disableOriginalConstructor()
            ->getMock();

        $itemOneMock->expects($this->any())
            ->method('getProduct')
            ->willReturn($productOneMock);
        $itemTwoMock->expects($this->any())
            ->method('getProduct')
            ->willReturn($productTwoMock);

        $collection = [$itemOneMock, $itemTwoMock];

        /** @var \Magento\Wishlist\Model\Wishlist|\PHPUnit_Framework_MockObject_MockObject $wishlistMock */
        $wishlistMock = $this->getMockBuilder('Magento\Wishlist\Model\Wishlist')
            ->setMethods(['isOwner', 'getItemCollection', 'getId', 'getSharingCode', 'save'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->sessionMock->expects($this->once())
            ->method('getCustomerId')
            ->willReturn($sessionCustomerId);

        $wishlistMock->expects($this->once())
            ->method('isOwner')
            ->with($sessionCustomerId)
            ->willReturn($isOwner);

        /** @var Collection|\PHPUnit_Framework_MockObject_MockObject $collectionMock */
        $collectionMock = $this->getMockBuilder('Magento\Wishlist\Model\ResourceModel\Item\Collection')
            ->disableOriginalConstructor()
            ->getMock();

        $wishlistMock->expects($this->once())
            ->method('getItemCollection')
            ->willReturn($collectionMock);

        $collectionMock->expects($this->once())
            ->method('setVisibilityFilter')
            ->with(true)
            ->willReturn($collection);

        $productOneMock->expects($this->once())
            ->method('getDisableAddToCart')
            ->willReturn(false);
        $productOneMock->expects($this->once())
            ->method('setDisableAddToCart')
            ->with(false);
        $productTwoMock->expects($this->once())
            ->method('getDisableAddToCart')
            ->willReturn(true);
        $productTwoMock->expects($this->once())
            ->method('setDisableAddToCart')
            ->with(true);

        $itemOneMock->expects($this->once())
            ->method('unsProduct');
        $itemTwoMock->expects($this->once())
            ->method('unsProduct');
        $itemOneMock->expects($this->exactly(2))
            ->method('getId')
            ->willReturn($itemOneId);
        $itemTwoMock->expects($this->exactly(2))
            ->method('getId')
            ->willReturn($itemTwoId);

        $this->quantityProcessorMock->expects($this->exactly(2))
            ->method('process')
            ->willReturnMap(
                [
                    [$qtys[$itemOneId], $qtys[$itemOneId]],
                    [$qtys[$itemTwoId], $qtys[$itemTwoId]],
                ]
            );
        $itemOneMock->expects($this->once())
            ->method('setQty')
            ->with($qtys[$itemOneId])
            ->willReturnSelf();
        $itemTwoMock->expects($this->once())
            ->method('setQty')
            ->with($qtys[$itemTwoId])
            ->willReturnSelf();

        $itemOneMock->expects($this->once())
            ->method('addToCart')
            ->with($this->cartMock, $isOwner)
            ->willThrowException(new \Magento\Catalog\Model\Product\Exception(__('Product Exception.')));
        $itemTwoMock->expects($this->once())
            ->method('addToCart')
            ->with($this->cartMock, $isOwner)
            ->willThrowException(new \Magento\Framework\Exception\LocalizedException(__('Localized Exception.')));

        /** @var \Magento\Quote\Model\Quote|\PHPUnit_Framework_MockObject_MockObject $collectionMock */
        $quoteMock = $this->getMockBuilder('Magento\Quote\Model\Quote')
            ->disableOriginalConstructor()
            ->getMock();

        $this->cartMock->expects($this->exactly(4))
            ->method('getQuote')
            ->willReturn($quoteMock);

        /** @var \Magento\Quote\Model\Quote\Item|\PHPUnit_Framework_MockObject_MockObject $collectionMock */
        $itemMock = $this->getMockBuilder('Magento\Quote\Model\Quote\Item')
            ->disableOriginalConstructor()
            ->getMock();

        $quoteMock->expects($this->exactly(2))
            ->method('getItemByProduct')
            ->willReturn($itemMock);

        $quoteMock->expects($this->exactly(2))
            ->method('deleteItem')
            ->with($itemMock)
            ->willReturnSelf();

        $wishlistMock->expects($this->once())
            ->method('getSharingCode')
            ->willReturn($sharingCode);

        $this->urlBuilderMock->expects($this->once())
            ->method('getUrl')
            ->with('wishlist/shared', ['code' => $sharingCode])
            ->willReturn($indexUrl);

        $this->cartHelperMock->expects($this->once())
            ->method('getShouldRedirectToCart')
            ->with(null)
            ->willReturn(false);

        $this->redirectMock->expects($this->exactly(2))
            ->method('getRefererUrl')
            ->willReturn($redirectUrl);

        $productOneMock->expects($this->any())
            ->method('getName')
            ->willReturn($productOneName);
        $productTwoMock->expects($this->any())
            ->method('getName')
            ->willReturn($productTwoName);

        $this->managerMock->expects($this->at(0))
            ->method('addError')
            ->with(__('%1 for "%2".', 'Localized Exception', $productTwoName), null)
            ->willReturnSelf();

        $this->managerMock->expects($this->at(1))
            ->method('addError')
            ->with(
                __(
                    'We couldn\'t add the following product(s) to the shopping cart: %1.',
                    '"' . $productOneName . '"'
                ),
                null
            )->willReturnSelf();

        $this->wishlistHelperMock->expects($this->once())
            ->method('calculate')
            ->willReturnSelf();

        $this->assertEquals($indexUrl, $this->model->moveAllToCart($wishlistMock, $qtys));
    }

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testMoveAllToCartWithException()
    {
        $wishlistId = 7;
        $sessionCustomerId = 23;
        $itemOneId = 14;
        $itemTwoId = 17;
        $productOneName = 'product one';
        $productTwoName = 'product two';
        $qtys = [14 => 21];
        $isOwner = true;
        $indexUrl = 'index_url';

        /** @var \Magento\Wishlist\Model\Item|\PHPUnit_Framework_MockObject_MockObject $itemOneMock */
        $itemOneMock = $this->getMockBuilder('Magento\Wishlist\Model\Item')
            ->setMethods(['getProduct', 'unsProduct', 'getId', 'setQty', 'addToCart', 'delete', 'getProductUrl'])
            ->disableOriginalConstructor()
            ->getMock();
        /** @var \Magento\Wishlist\Model\Item|\PHPUnit_Framework_MockObject_MockObject $itemTwoMock */
        $itemTwoMock = $this->getMockBuilder('Magento\Wishlist\Model\Item')
            ->setMethods(['getProduct', 'unsProduct', 'getId', 'setQty', 'addToCart', 'delete', 'getProductUrl'])
            ->disableOriginalConstructor()
            ->getMock();

        /** @var \Magento\Catalog\Model\Product|\PHPUnit_Framework_MockObject_MockObject $productOneMock */
        $productOneMock = $this->getMockBuilder('Magento\Catalog\Model\Product')
            ->setMethods(['getDisableAddToCart', 'setDisableAddToCart', 'getName'])
            ->disableOriginalConstructor()
            ->getMock();
        /** @var \Magento\Catalog\Model\Product|\PHPUnit_Framework_MockObject_MockObject $productTwoMock */
        $productTwoMock = $this->getMockBuilder('Magento\Catalog\Model\Product')
            ->setMethods(['getDisableAddToCart', 'setDisableAddToCart', 'getName'])
            ->disableOriginalConstructor()
            ->getMock();

        $itemOneMock->expects($this->any())
            ->method('getProduct')
            ->willReturn($productOneMock);
        $itemTwoMock->expects($this->any())
            ->method('getProduct')
            ->willReturn($productTwoMock);

        $collection = [$itemOneMock, $itemTwoMock];

        /** @var \Magento\Wishlist\Model\Wishlist|\PHPUnit_Framework_MockObject_MockObject $wishlistMock */
        $wishlistMock = $this->getMockBuilder('Magento\Wishlist\Model\Wishlist')
            ->disableOriginalConstructor()
            ->getMock();

        $this->sessionMock->expects($this->once())
            ->method('getCustomerId')
            ->willReturn($sessionCustomerId);

        $wishlistMock->expects($this->once())
            ->method('isOwner')
            ->with($sessionCustomerId)
            ->willReturn($isOwner);
        $wishlistMock->expects($this->once())
            ->method('getId')
            ->willReturn($wishlistId);

        /** @var Collection|\PHPUnit_Framework_MockObject_MockObject $collectionMock */
        $collectionMock = $this->getMockBuilder('Magento\Wishlist\Model\ResourceModel\Item\Collection')
            ->disableOriginalConstructor()
            ->getMock();

        $wishlistMock->expects($this->once())
            ->method('getItemCollection')
            ->willReturn($collectionMock);

        $collectionMock->expects($this->once())
            ->method('setVisibilityFilter')
            ->with(true)
            ->willReturn($collection);

        $productOneMock->expects($this->once())
            ->method('getDisableAddToCart')
            ->willReturn(true);
        $productOneMock->expects($this->once())
            ->method('setDisableAddToCart')
            ->with(true);
        $productTwoMock->expects($this->once())
            ->method('getDisableAddToCart')
            ->willReturn(false);
        $productTwoMock->expects($this->once())
            ->method('setDisableAddToCart')
            ->with(false);

        $itemOneMock->expects($this->once())
            ->method('unsProduct');
        $itemTwoMock->expects($this->once())
            ->method('unsProduct');
        $itemOneMock->expects($this->exactly(2))
            ->method('getId')
            ->willReturn($itemOneId);
        $itemTwoMock->expects($this->once())
            ->method('getId')
            ->willReturn($itemTwoId);

        $this->quantityProcessorMock->expects($this->once())
            ->method('process')
            ->with($qtys[$itemOneId])
            ->willReturnArgument(0);
        $itemOneMock->expects($this->once())
            ->method('setQty')
            ->with($qtys[$itemOneId])
            ->willReturnSelf();
        $itemTwoMock->expects($this->never())
            ->method('setQty');

        $itemOneMock->expects($this->once())
            ->method('addToCart')
            ->with($this->cartMock, $isOwner)
            ->willReturn(true);

        $exception = new \Exception('Exception.');
        $itemTwoMock->expects($this->once())
            ->method('addToCart')
            ->with($this->cartMock, $isOwner)
            ->willThrowException($exception);

        $this->loggerMock->expects($this->once())
            ->method('critical')
            ->with($exception, []);

        $this->managerMock->expects($this->at(0))
            ->method('addError')
            ->with(__('We can\'t add this item to your shopping cart right now.'), null)
            ->willReturnSelf();

        $this->wishlistHelperMock->expects($this->once())
            ->method('getListUrl')
            ->with($wishlistId)
            ->willReturn($indexUrl);

        $this->cartHelperMock->expects($this->once())
            ->method('getShouldRedirectToCart')
            ->with(null)
            ->willReturn(false);

        $this->redirectMock->expects($this->once())
            ->method('getRefererUrl')
            ->willReturn('');

        $wishlistMock->expects($this->once())
            ->method('save')
            ->willThrowException(new \Exception());

        $this->managerMock->expects($this->at(1))
            ->method('addError')
            ->with(__('We can\'t update the Wish List right now.'), null)
            ->willReturnSelf();

        $productOneMock->expects($this->any())
            ->method('getName')
            ->willReturn($productOneName);
        $productTwoMock->expects($this->any())
            ->method('getName')
            ->willReturn($productTwoName);

        $this->managerMock->expects($this->once())
            ->method('addSuccess')
            ->with(__('%1 product(s) have been added to shopping cart: %2.', 1, '"' . $productOneName . '"'), null)
            ->willReturnSelf();

        $this->cartMock->expects($this->once())
            ->method('save')
            ->willReturnSelf();

        /** @var \Magento\Quote\Model\Quote|\PHPUnit_Framework_MockObject_MockObject $collectionMock */
        $quoteMock = $this->getMockBuilder('Magento\Quote\Model\Quote')
            ->disableOriginalConstructor()
            ->getMock();

        $this->cartMock->expects($this->once())
            ->method('getQuote')
            ->willReturn($quoteMock);

        $quoteMock->expects($this->once())
            ->method('collectTotals')
            ->willReturnSelf();

        $this->wishlistHelperMock->expects($this->once())
            ->method('calculate')
            ->willReturnSelf();

        $this->assertEquals($indexUrl, $this->model->moveAllToCart($wishlistMock, $qtys));
    }
}
