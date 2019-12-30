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
use Magento\Wishlist\Controller\Index\Update;
use Magento\Wishlist\Controller\WishlistProviderInterface;
use Magento\Wishlist\Model\LocaleQuantityProcessor;
use PHPUnit\Framework\TestCase;

/**
 * Test for upate controller wishlist
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

        $this->context->expects($this->once())
                      ->method('getResultFactory')
                      ->willReturn($this->resultFactory);

        $this->resultFactory->expects($this->any())
                              ->method('create')
                              ->willReturn($this->resultRedirect);
        $this->context->expects($this->any())
            ->method('getRequest')
            ->willReturn($this->requestMock);

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
     * Check if there is not post value result redirect returned.
     *
     * @return void
     */
    public function testUpdate(): void
    {
        $this->formKeyValidator->expects($this->once())
                               ->method('validate')
                               ->willReturn(true);

        $wishlist = $this->createMock(\Magento\Wishlist\Model\Wishlist::class);
        $this->wishlistProvider->expects($this->once())
            ->method('getWishlist')
            ->willReturn($wishlist);
        $this->requestMock->expects($this->once())
            ->method('getPostValue')
            ->willReturn(null);
        $this->assertEquals($this->resultRedirect, $this->updateController->execute());
    }
}
