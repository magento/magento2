<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Wishlist\Test\Unit\Controller\Index;

use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Wishlist\Controller\Index\Update;
use Magento\Wishlist\Controller\WishlistProviderInterface;
use Magento\Wishlist\Helper\Data;
use Magento\Wishlist\Model\Item;
use Magento\Wishlist\Model\LocaleQuantityProcessor;
use PHPUnit\Framework\TestCase;

/**
 * Test for upate controller wishlist
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class UpdateTest extends TestCase
{
    /**
     * @var Validator $formKeyValidator
     */
    private $formKeyValidator;

    /**
     * @var WishlistProviderInterface $wishlistProvider
     */
    private $wishlistProvider;

    /**
     * @var LocaleQuantityProcessor $quantityProcessor
     */
    private $quantityProcessor;

    /**
     * @var Update $updateController
     */
    private $updateController;

    /**
     * @var $context
     */
    private $context;

    /**
     * @var Redirect $resultRedirect
     */
    private $resultRedirect;

    /**
     * @var ResultFactory $resultFatory
     */
    private $resultFactory;

    /**
     * @var RequestInterface $requestMock
     */
    private $requestMock;

    /**
     * @var ObjectManagerInterface $objectManagerMock
     */
    private $objectManagerMock;

    /**
     * @var ManagerInterface $messageManager
     */
    private $messageManager;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->formKeyValidator = $this->createMock(Validator::class);
        $this->wishlistProvider = $this->createMock(WishlistProviderInterface::class);
        $this->quantityProcessor = $this->createMock(LocaleQuantityProcessor::class);
        $this->context = $this->createMock(Context::class);
        $this->resultRedirect = $this->createMock(Redirect::class);
        $this->resultFactory = $this->createPartialMock(ResultFactory::class, ['create']);
        $this->requestMock = $this->getMockBuilder(RequestInterface::class)
            ->setMethods(['getPostValue'])
            ->getMockForAbstractClass();
        $this->objectManagerMock = $this->createMock(ObjectManagerInterface::class);

        $this->context->expects($this->once())
            ->method('getResultFactory')
            ->willReturn($this->resultFactory);
        $this->context->expects($this->once())
            ->method('getObjectManager')
            ->willReturn($this->objectManagerMock);

        $this->resultFactory->expects($this->any())
                              ->method('create')
                              ->willReturn($this->resultRedirect);
        $this->context->expects($this->any())
            ->method('getRequest')
            ->willReturn($this->requestMock);

        $this->messageManager = $this->createMock(ManagerInterface::class);
        $this->context->expects($this->any())
            ->method('getMessageManager')
            ->willReturn($this->messageManager);

        $this->updateController = new Update(
            $this->context,
            $this->formKeyValidator,
            $this->wishlistProvider,
            $this->quantityProcessor
        );
    }

    /**
     * Test for update method Wishlist controller.
     *
     * @dataProvider getWishlistDataProvider
     * @return void
     */
    public function testUpdate(array $wishlistDataProvider): void
    {
        $this->formKeyValidator->expects($this->once())
            ->method('validate')
            ->willReturn(true);

        $wishlist = $this->createMock(\Magento\Wishlist\Model\Wishlist::class);

        $this->wishlistProvider->expects($this->once())
            ->method('getWishlist')
            ->willReturn($wishlist);
        $wishlist->expects($this->exactly(2))
            ->method('getId')
            ->willReturn($wishlistDataProvider['wishlist_data']['id']);
        $this->requestMock->expects($this->once())
            ->method('getPostValue')
            ->willReturn($wishlistDataProvider['post_data']);
        $this->resultRedirect->expects($this->once())
            ->method('setPath')
            ->with('*', ['wishlist_id' => $wishlistDataProvider['wishlist_data']['id']]);
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
            ->willReturn($wishlistDataProvider['wishlist_data']['id']);
        $itemMock->expects($this->once())
            ->method('getDescription')
            ->willReturn('');
        $itemMock->expects($this->once())
            ->method('setDescription')
            ->willReturnSelf();
        $itemMock->expects($this->once())
            ->method('setQty')
            ->willReturnSelf();
        $dataMock = $this->createMock(Data::class);

        $this->objectManagerMock->expects($this->exactly(2))
            ->method('get')
            ->with(Data::class)
            ->willReturn($dataMock);
        $dataMock->expects($this->once())
            ->method('defaultCommentString')
            ->willReturn('');
        $dataMock->expects($this->once())
            ->method('calculate');
        $this->quantityProcessor->expects($this->once())
            ->method('process')
            ->willReturn($wishlistDataProvider['post_data']['qty']);

        $productMock = $this->getMockBuilder(\Magento\Catalog\Model\Product::class)
            ->disableOriginalConstructor()
            ->getMock();
        $itemMock->expects($this->once())
            ->method('getProduct')
            ->willReturn($productMock);
        $productMock->expects($this->once())
            ->method('getName')
            ->willReturn('product');
        $this->messageManager->expects($this->once())
            ->method('addSuccessMessage');
        $this->assertEquals($this->resultRedirect, $this->updateController->execute());
    }

    /**
     * Check if wishlist not availbale, and exception is shown
     */
    public function testUpdateWithNotFoundException()
    {
        $this->formKeyValidator->expects($this->once())
            ->method('validate')
            ->willReturn(true);
        $this->wishlistProvider->expects($this->once())
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
        return [
            [
                [
                    'wishlist_data' => [
                        'id' => 1,

                    ],
                    'post_data' => [
                        'qty' => [1 => 12],
                        'description' => [
                            1 => 'Description for item_id 1'
                        ]
                    ]
                ]
            ]
        ];
    }
}
