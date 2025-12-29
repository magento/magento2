<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Controller\Product\Compare;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Controller\Product\Compare\Index;
use Magento\Catalog\Model\Product\Compare\ItemFactory;
use Magento\Catalog\Model\Product\Compare\ListCompare;
use Magento\Catalog\Model\ResourceModel\Product\Compare\Item;
use Magento\Catalog\Model\ResourceModel\Product\Compare\Item\CollectionFactory;
use Magento\Catalog\Model\Session as CatalogSession;
use Magento\Customer\Model\Session;
use Magento\Customer\Model\Visitor;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use Magento\Framework\Url\DecoderInterface;
use Magento\Framework\View\Result\PageFactory;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;

use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class IndexTest extends TestCase
{
    use MockCreationTrait;
    /** @var Index */
    protected $index;

    /** @var Context|MockObject */
    protected $contextMock;

    /** @var ItemFactory|MockObject */
    protected $itemFactoryMock;

    /** @var Item\CollectionFactory|MockObject */
    protected $collectionFactoryMock;

    /** @var Session|MockObject */
    protected $sessionMock;

    /** @var Visitor|MockObject */
    protected $visitorMock;

    /** @var ListCompare|MockObject */
    protected $listCompareMock;

    /** @var CatalogSession|MockObject */
    protected $catalogSession;

    /** @var StoreManagerInterface|MockObject */
    protected $storeManagerMock;

    /** @var Validator|MockObject */
    protected $formKeyValidatorMock;

    /** @var RedirectFactory|MockObject */
    protected $redirectFactoryMock;

    /** @var PageFactory|MockObject */
    protected $pageFactoryMock;

    /** @var ProductRepositoryInterface|MockObject */
    protected $productRepositoryMock;

    /** @var DecoderInterface|MockObject */
    protected $decoderMock;

    /** @var RequestInterface|MockObject */
    protected $request;

    /** @var ResponseInterface|MockObject */
    protected $response;

    protected function setUp(): void
    {
        $this->contextMock = $this->createPartialMock(
            Context::class,
            ['getRequest', 'getResponse', 'getResultRedirectFactory']
        );
        $this->request = $this->createMock(RequestInterface::class);
        $this->response = $this->createMock(ResponseInterface::class);
        $this->redirectFactoryMock = $this->createPartialMock(
            RedirectFactory::class,
            ['create']
        );
        $this->contextMock->method('getRequest')->willReturn($this->request);
        $this->contextMock->method('getResponse')->willReturn($this->response);
        $this->contextMock->method('getResultRedirectFactory')->willReturn($this->redirectFactoryMock);

        $this->itemFactoryMock = $this->createPartialMock(
            ItemFactory::class,
            ['create']
        );
        $this->collectionFactoryMock = $this->createPartialMock(
            CollectionFactory::class,
            ['create']
        );
        $this->sessionMock = $this->createMock(Session::class);
        $this->visitorMock = $this->createMock(Visitor::class);
        $this->listCompareMock = $this->createMock(ListCompare::class);
        $this->catalogSession = $this->createPartialMockWithReflection(
            CatalogSession::class,
            ['setBeforeCompareUrl', 'getBeforeCompareUrl']
        );
        $this->catalogSession->method('setBeforeCompareUrl')->willReturnSelf();
        $this->storeManagerMock = $this->createMock(StoreManagerInterface::class);
        $this->formKeyValidatorMock = $this->createMock(Validator::class);
        $this->pageFactoryMock = $this->createMock(PageFactory::class);
        $this->productRepositoryMock = $this->createMock(ProductRepositoryInterface::class);
        $this->decoderMock = $this->createMock(DecoderInterface::class);

        $this->index = new Index(
            $this->contextMock,
            $this->itemFactoryMock,
            $this->collectionFactoryMock,
            $this->sessionMock,
            $this->visitorMock,
            $this->listCompareMock,
            $this->catalogSession,
            $this->storeManagerMock,
            $this->formKeyValidatorMock,
            $this->pageFactoryMock,
            $this->productRepositoryMock,
            $this->decoderMock
        );
    }

    public function testExecute()
    {
        $beforeUrl = 'test_url';
        $this->request->expects($this->any())
            ->method('getParam')
            ->willReturnMap(
                [
                    ['uenc', null, $beforeUrl],
                ]
            );
        $this->decoderMock->expects($this->once())
            ->method('decode')
            ->with($beforeUrl)
            ->willReturn($beforeUrl . '1');
        $this->catalogSession->expects($this->once())
            ->method('setBeforeCompareUrl')
            ->with($beforeUrl . '1')
            ->willReturnSelf();
        $this->redirectFactoryMock->expects($this->never())->method('create');
        $this->index->execute();
    }
}
