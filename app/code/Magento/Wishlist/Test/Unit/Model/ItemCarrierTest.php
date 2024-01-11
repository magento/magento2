<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Wishlist\Test\Unit\Model;

use Exception;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Exception as ProductException;
use Magento\Checkout\Helper\Cart as HelperCart;
use Magento\Checkout\Model\Cart;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Response\RedirectInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\UrlInterface;
use Magento\Quote\Model\Quote;
use Magento\Wishlist\Helper\Data;
use Magento\Wishlist\Model\Item;
use Magento\Wishlist\Model\ItemCarrier;
use Magento\Wishlist\Model\LocaleQuantityProcessor;
use Magento\Wishlist\Model\ResourceModel\Item\Collection;
use Magento\Wishlist\Model\Wishlist;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ItemCarrierTest extends TestCase
{
    /**
     * @var ItemCarrier
     */
    protected $model;

    /**
     * @var Session|MockObject
     */
    protected $sessionMock;

    /**
     * @var LocaleQuantityProcessor|MockObject
     */
    protected $quantityProcessorMock;

    /**
     * @var Cart|MockObject
     */
    protected $cartMock;

    /**
     * @var LoggerInterface|MockObject
     */
    protected $loggerMock;

    /**
     * @var Data|MockObject
     */
    protected $wishlistHelperMock;

    /**
     * @var HelperCart|MockObject
     */
    protected $cartHelperMock;

    /**
     * @var UrlInterface|MockObject
     */
    protected $urlBuilderMock;

    /**
     * @var ManagerInterface|MockObject
     */
    protected $managerMock;

    /**
     * @var RedirectInterface|MockObject
     */
    protected $redirectMock;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->sessionMock = $this->getMockBuilder(Session::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->quantityProcessorMock = $this->getMockBuilder(LocaleQuantityProcessor::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->cartMock = $this->getMockBuilder(Cart::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->loggerMock = $this->getMockBuilder(LoggerInterface::class)
            ->getMockForAbstractClass();
        $this->wishlistHelperMock = $this->getMockBuilder(Data::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->cartHelperMock = $this->getMockBuilder(HelperCart::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->urlBuilderMock = $this->getMockBuilder(UrlInterface::class)
            ->getMockForAbstractClass();
        $this->managerMock = $this->getMockBuilder(ManagerInterface::class)
            ->getMockForAbstractClass();
        $this->redirectMock = $this->getMockBuilder(RedirectInterface::class)
            ->getMockForAbstractClass();

        $this->model = new ItemCarrier(
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
     * @return void
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testMoveAllToCart(): void
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

        /** @var Item|MockObject $itemOneMock */
        $itemOneMock = $this->getMockBuilder(Item::class)
            ->onlyMethods(
                [
                    'getProduct',
                    'getId',
                    'setQty',
                    'addToCart',
                    'delete',
                    'getProductUrl'
                ]
            )
            ->addMethods(['unsProduct'])
            ->disableOriginalConstructor()
            ->getMock();
        /** @var Item|MockObject $itemTwoMock */
        $itemTwoMock = $this->getMockBuilder(Item::class)
            ->onlyMethods(
                [
                    'getProduct',
                    'getId',
                    'setQty',
                    'addToCart',
                    'delete',
                    'getProductUrl'
                ]
            )
            ->addMethods(['unsProduct'])
            ->disableOriginalConstructor()
            ->getMock();

        /** @var Product|MockObject $productOneMock */
        $productOneMock = $this->getMockBuilder(Product::class)
            ->onlyMethods(['getName'])
            ->addMethods(['getDisableAddToCart', 'setDisableAddToCart'])
            ->disableOriginalConstructor()
            ->getMock();
        /** @var Product|MockObject $productTwoMock */
        $productTwoMock = $this->getMockBuilder(Product::class)
            ->onlyMethods(['getName'])
            ->addMethods(['getDisableAddToCart', 'setDisableAddToCart'])
            ->disableOriginalConstructor()
            ->getMock();

        $itemOneMock->expects($this->any())
            ->method('getProduct')
            ->willReturn($productOneMock);
        $itemTwoMock->expects($this->any())
            ->method('getProduct')
            ->willReturn($productTwoMock);

        $collection = [$itemOneMock, $itemTwoMock];

        /** @var Wishlist|MockObject $wishlistMock */
        $wishlistMock = $this->getMockBuilder(Wishlist::class)
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

        /** @var Collection|MockObject $collectionMock */
        $collectionMock = $this->getMockBuilder(Collection::class)
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
            ->method('addSuccessMessage')
            ->with(__('%1 product(s) have been added to shopping cart: %2.', 1, '"' . $productTwoName . '"'), null)
            ->willReturnSelf();

        $this->cartMock->expects($this->once())
            ->method('save')
            ->willReturnSelf();

        /** @var Quote|MockObject $collectionMock */
        $quoteMock = $this->getMockBuilder(Quote::class)
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
     * @return void
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testMoveAllToCartWithNotSalableAndOptions(): void
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

        /** @var Item|MockObject $itemOneMock */
        $itemOneMock = $this->getMockBuilder(Item::class)
            ->onlyMethods(
                [
                    'getProduct',
                    'getId',
                    'setQty',
                    'addToCart',
                    'delete',
                    'getProductUrl'
                ]
            )
            ->addMethods(['unsProduct'])
            ->disableOriginalConstructor()
            ->getMock();
        /** @var Item|MockObject $itemTwoMock */
        $itemTwoMock = $this->getMockBuilder(Item::class)
            ->onlyMethods(
                [
                    'getProduct',
                    'getId',
                    'setQty',
                    'addToCart',
                    'delete',
                    'getProductUrl'
                ]
            )
            ->addMethods(['unsProduct'])
            ->disableOriginalConstructor()
            ->getMock();

        /** @var Product|MockObject $productOneMock */
        $productOneMock = $this->getMockBuilder(Product::class)
            ->onlyMethods(['getName'])
            ->addMethods(['getDisableAddToCart', 'setDisableAddToCart'])
            ->disableOriginalConstructor()
            ->getMock();
        /** @var Product|MockObject $productTwoMock */
        $productTwoMock = $this->getMockBuilder(Product::class)
            ->onlyMethods(['getName'])
            ->addMethods(['getDisableAddToCart', 'setDisableAddToCart'])
            ->disableOriginalConstructor()
            ->getMock();

        $itemOneMock->expects($this->any())
            ->method('getProduct')
            ->willReturn($productOneMock);
        $itemTwoMock->expects($this->any())
            ->method('getProduct')
            ->willReturn($productTwoMock);

        $collection = [$itemOneMock, $itemTwoMock];

        /** @var Wishlist|MockObject $wishlistMock */
        $wishlistMock = $this->getMockBuilder(Wishlist::class)
            ->onlyMethods(['isOwner', 'getItemCollection', 'getId', 'save'])
            ->addMethods(['getSharingCode'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->sessionMock->expects($this->once())
            ->method('getCustomerId')
            ->willReturn($sessionCustomerId);

        $wishlistMock->expects($this->once())
            ->method('isOwner')
            ->with($sessionCustomerId)
            ->willReturn($isOwner);

        /** @var Collection|MockObject $collectionMock */
        $collectionMock = $this->getMockBuilder(Collection::class)
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
                    [$qtys[$itemTwoId], $qtys[$itemTwoId]]
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
            ->willThrowException(new ProductException(__('Product Exception.')));
        $itemTwoMock->expects($this->once())
            ->method('addToCart')
            ->with($this->cartMock, $isOwner)
            ->willThrowException(new LocalizedException(__('Localized Exception.')));

        /** @var Quote|MockObject $collectionMock */
        $quoteMock = $this->getMockBuilder(Quote::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->cartMock->expects($this->exactly(4))
            ->method('getQuote')
            ->willReturn($quoteMock);

        /** @var Quote\Item|MockObject $collectionMock */
        $itemMock = $this->getMockBuilder(Quote\Item::class)
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

        $this->managerMock
            ->method('addErrorMessage')
            ->willReturnCallback(function ($arg1, $arg2) use ($productOneName, $productTwoName) {
                if ($arg1 == __('%1 for "%2".', 'Localized Exception', $productTwoName) && $arg2 === null) {
                    return $this->managerMock;
                } elseif ($arg1 == __('We couldn\'t add the following product(s) to the shopping cart: %1.', '"' .
                        $productOneName . '"') && $arg2 === null) {
                    return $this->managerMock;
                }
            });

        $this->wishlistHelperMock->expects($this->once())
            ->method('calculate')
            ->willReturnSelf();

        $this->assertEquals($indexUrl, $this->model->moveAllToCart($wishlistMock, $qtys));
    }

    /**
     * @return void
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testMoveAllToCartWithException(): void
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

        /** @var Item|MockObject $itemOneMock */
        $itemOneMock = $this->getMockBuilder(Item::class)
            ->onlyMethods(
                [
                    'getProduct',
                    'getId',
                    'setQty',
                    'addToCart',
                    'delete',
                    'getProductUrl'
                ]
            )
            ->addMethods(['unsProduct'])
            ->disableOriginalConstructor()
            ->getMock();
        /** @var Item|MockObject $itemTwoMock */
        $itemTwoMock = $this->getMockBuilder(Item::class)
            ->onlyMethods(
                [
                    'getProduct',
                    'getId',
                    'setQty',
                    'addToCart',
                    'delete',
                    'getProductUrl'
                ]
            )
            ->addMethods(['unsProduct'])
            ->disableOriginalConstructor()
            ->getMock();

        /** @var Product|MockObject $productOneMock */
        $productOneMock = $this->getMockBuilder(Product::class)
            ->onlyMethods(['getName'])
            ->addMethods(['getDisableAddToCart', 'setDisableAddToCart'])
            ->disableOriginalConstructor()
            ->getMock();
        /** @var Product|MockObject $productTwoMock */
        $productTwoMock = $this->getMockBuilder(Product::class)
            ->onlyMethods(['getName'])
            ->addMethods(['getDisableAddToCart', 'setDisableAddToCart'])
            ->disableOriginalConstructor()
            ->getMock();

        $itemOneMock->expects($this->any())
            ->method('getProduct')
            ->willReturn($productOneMock);
        $itemTwoMock->expects($this->any())
            ->method('getProduct')
            ->willReturn($productTwoMock);

        $collection = [$itemOneMock, $itemTwoMock];

        /** @var Wishlist|MockObject $wishlistMock */
        $wishlistMock = $this->getMockBuilder(Wishlist::class)
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

        /** @var Collection|MockObject $collectionMock */
        $collectionMock = $this->getMockBuilder(Collection::class)
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

        $exception = new Exception('Exception.');
        $itemTwoMock->expects($this->once())
            ->method('addToCart')
            ->with($this->cartMock, $isOwner)
            ->willThrowException($exception);

        $this->loggerMock->expects($this->once())
            ->method('critical')
            ->with($exception, []);

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
            ->willThrowException(new Exception());

        $this->managerMock
            ->method('addErrorMessage')
            ->willReturnCallback(function ($arg1, $arg2) {
                if ($arg1 == __('We can\'t add this item to your shopping cart right now.' && $arg2 === null) ||
                    $arg1 == __('We can\'t update the Wish List right now.') && $arg2 === null) {
                    return $this->managerMock;
                }
            });

        $productOneMock->expects($this->any())
            ->method('getName')
            ->willReturn($productOneName);
        $productTwoMock->expects($this->any())
            ->method('getName')
            ->willReturn($productTwoName);

        $this->managerMock->expects($this->once())
            ->method('addSuccessMessage')
            ->with(__('%1 product(s) have been added to shopping cart: %2.', 1, '"' . $productOneName . '"'), null)
            ->willReturnSelf();

        $this->cartMock->expects($this->once())
            ->method('save')
            ->willReturnSelf();

        /** @var Quote|MockObject $collectionMock */
        $quoteMock = $this->getMockBuilder(Quote::class)
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
