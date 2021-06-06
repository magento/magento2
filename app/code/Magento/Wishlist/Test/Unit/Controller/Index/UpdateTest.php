<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Wishlist\Test\Unit\Controller\Index;

use Magento\Backend\Model\View\Result\Redirect;
use Magento\Catalog\Model\Product;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Wishlist\Controller\Index\Update;
use Magento\Wishlist\Controller\WishlistProviderInterface;
use Magento\Wishlist\Helper\Data;
use Magento\Wishlist\Model\Item;
use Magento\Wishlist\Model\LocaleQuantityProcessor;
use Magento\Wishlist\Model\Wishlist;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test for upate controller wishlist
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class UpdateTest extends TestCase
{
    private const STUB_ITEM_ID = 1;

    private const STUB_WISHLIST_PRODUCT_QTY = 21;

    /**
     * @var MockObject|Validator $formKeyValidatorMock
     */
    private $formKeyValidatorMock;

    /**
     * @var MockObject|WishlistProviderInterface $wishlistProviderMock
     */
    private $wishlistProviderMock;

    /**
     * @var MockObject|LocaleQuantityProcessor $quantityProcessorMock
     */
    private $quantityProcessorMock;

    /**
     * @var Update $updateController
     */
    private $updateController;

    /**
     * @var MockObject|Context$contextMock
     */
    private $contextMock;

    /**
     * @var MockObject|Redirect $resultRedirectMock
     */
    private $resultRedirectMock;

    /**
     * @var MockObject|ResultFactory $resultFatoryMock
     */
    private $resultFactoryMock;

    /**
     * @var MockObject|RequestInterface $requestMock
     */
    private $requestMock;

    /**
     * @var MockObject|ObjectManagerInterface $objectManagerMock
     */
    private $objectManagerMock;

    /**
     * @var MockObject|ManagerInterface $messageManagerMock
     */
    private $messageManagerMock;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->formKeyValidatorMock = $this->createMock(Validator::class);
        $this->wishlistProviderMock = $this->getMockForAbstractClass(WishlistProviderInterface::class);
        $this->quantityProcessorMock = $this->createMock(LocaleQuantityProcessor::class);
        $this->contextMock = $this->createMock(Context::class);
        $this->resultRedirectMock = $this->createMock(Redirect::class);
        $this->resultFactoryMock = $this->createPartialMock(ResultFactory::class, ['create']);
        $this->messageManagerMock = $this->getMockForAbstractClass(ManagerInterface::class);
        $this->objectManagerMock = $this->getMockForAbstractClass(ObjectManagerInterface::class);
        $this->requestMock = $this->getMockBuilder(RequestInterface::class)
            ->setMethods(['getPostValue'])
            ->getMockForAbstractClass();

        $this->resultFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($this->resultRedirectMock);
        $this->contextMock->expects($this->once())
            ->method('getResultFactory')
            ->willReturn($this->resultFactoryMock);
        $this->contextMock->expects($this->once())
            ->method('getObjectManager')
            ->willReturn($this->objectManagerMock);
        $this->contextMock->expects($this->any())
            ->method('getRequest')
            ->willReturn($this->requestMock);
        $this->contextMock->expects($this->any())
            ->method('getMessageManager')
            ->willReturn($this->messageManagerMock);

        $objectManager = new ObjectManagerHelper($this);

        $this->updateController = $objectManager->getObject(
            Update::class,
            [
                'context' => $this->contextMock,
                '_formKeyValidator' => $this->formKeyValidatorMock,
                'wishlistProvider' => $this->wishlistProviderMock,
                'quantityProcessor' => $this->quantityProcessorMock
            ]
        );
    }

    /**
     * Test for update method Wishlist controller.
     *
     * @dataProvider getWishlistDataProvider
     * @param array $wishlistDataProvider
     * @param array $postData
     * @return void
     */
    public function testUpdate(array $wishlistDataProvider, array $postData): void
    {
        $wishlist = $this->createMock(Wishlist::class);
        $itemMock = $this->getMockBuilder(Item::class)
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'load',
                    'getId',
                    'getWishlistId',
                    'setQty',
                    'save',
                    'getDescription',
                    'setDescription',
                    'getProduct',
                    'getName'
                ]
            )->getMock();
        $dataMock = $this->createMock(Data::class);
        $productMock = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->formKeyValidatorMock->expects($this->once())
            ->method('validate')
            ->with($this->requestMock)
            ->willReturn(true);
        $this->wishlistProviderMock->expects($this->once())
            ->method('getWishlist')
            ->willReturn($wishlist);
        $wishlist->expects($this->exactly(2))
            ->method('getId')
            ->willReturn($wishlistDataProvider['id']);
        $this->requestMock->expects($this->once())
            ->method('getPostValue')
            ->willReturn($postData);
        $this->resultRedirectMock->expects($this->once())
            ->method('setPath')
            ->with('*', ['wishlist_id' => $wishlistDataProvider['id']]);
        $this->objectManagerMock->expects($this->once())
            ->method('create')
            ->with(Item::class)
            ->willReturn($itemMock);
        $itemMock->expects($this->once())
            ->method('load')
            ->with(1)
            ->willReturnSelf();
        $itemMock->expects($this->once())
            ->method('getWishLIstId')
            ->willReturn($wishlistDataProvider['id']);
        $itemMock->expects($this->once())
            ->method('getDescription')
            ->willReturn('');
        $itemMock->expects($this->once())
            ->method('setDescription')
            ->willReturnSelf();
        $itemMock->expects($this->once())
            ->method('setQty')
            ->willReturnSelf();
        $this->objectManagerMock->expects($this->exactly(2))
            ->method('get')
            ->with(Data::class)
            ->willReturn($dataMock);
        $dataMock->expects($this->once())
            ->method('defaultCommentString')
            ->willReturn('');
        $dataMock->expects($this->once())
            ->method('calculate');
        $this->quantityProcessorMock->expects($this->once())
            ->method('process')
            ->willReturn($postData['qty']);
        $itemMock->expects($this->once())
            ->method('getProduct')
            ->willReturn($productMock);
        $productMock->expects($this->once())
            ->method('getName')
            ->willReturn('product');
        $this->messageManagerMock->expects($this->once())
            ->method('addSuccessMessage');

        $this->assertEquals($this->resultRedirectMock, $this->updateController->execute());
    }

    /**
     * Verify update method if post data not available
     *
     * @dataProvider getWishlistDataProvider
     * @param array $wishlistDataProvider
     * @return void
     */
    public function testUpdateRedirectWhenNoPostData(array $wishlistDataProvider): void
    {
        $wishlist = $this->createMock(Wishlist::class);

        $this->formKeyValidatorMock->expects($this->once())
            ->method('validate')
            ->willReturn(true);
        $this->wishlistProviderMock->expects($this->once())
            ->method('getWishlist')
            ->willReturn($wishlist);
        $wishlist->expects($this->exactly(1))
            ->method('getId')
            ->willReturn($wishlistDataProvider['id']);
        $this->resultRedirectMock->expects($this->once())
            ->method('setPath')
            ->with('*', ['wishlist_id' => $wishlistDataProvider['id']]);
        $this->requestMock->expects($this->once())
            ->method('getPostValue')
            ->willReturn(null);

        $this->assertEquals($this->resultRedirectMock, $this->updateController->execute());
    }

    /**
     * Check if wishlist not availbale, and exception is shown
     *
     * @return void
     */
    public function testUpdateThrowsNotFoundExceptionWhenWishlistDoNotExist(): void
    {
        $this->formKeyValidatorMock->expects($this->once())
            ->method('validate')
            ->willReturn(true);
        $this->wishlistProviderMock->expects($this->once())
            ->method('getWishlist')
            ->willReturn(null);
        $this->expectException(NotFoundException::class);
        $this->updateController->execute();
    }

    /**
     * Dataprovider for Update test
     *
     * @return array
     */
    public function getWishlistDataProvider(): array
    {
        return
            [
                [
                    [
                        'id' => self::STUB_ITEM_ID
                    ],
                    [
                        'qty' => [self::STUB_ITEM_ID => self::STUB_WISHLIST_PRODUCT_QTY],
                        'description' => [self::STUB_ITEM_ID => 'Description for item_id 1']
                    ]
                ]
            ];
    }
}
