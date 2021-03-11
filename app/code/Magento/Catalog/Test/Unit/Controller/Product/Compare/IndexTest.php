<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Unit\Controller\Product\Compare;

use Magento\Catalog\Controller\Product\Compare\Index;

use Magento\Catalog\Model\ResourceModel\Product\Compare\Item;

/**
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class IndexTest extends \PHPUnit\Framework\TestCase
{
    /** @var \Magento\Catalog\Controller\Product\Compare\Index */
    protected $index;

    /** @var \Magento\Framework\App\Action\Context|\PHPUnit\Framework\MockObject\MockObject */
    protected $contextMock;

    /** @var \Magento\Catalog\Model\Product\Compare\ItemFactory|\PHPUnit\Framework\MockObject\MockObject */
    protected $itemFactoryMock;

    /** @var Item\CollectionFactory|\PHPUnit\Framework\MockObject\MockObject */
    protected $collectionFactoryMock;

    /** @var \Magento\Customer\Model\Session|\PHPUnit\Framework\MockObject\MockObject */
    protected $sessionMock;

    /** @var \Magento\Customer\Model\Visitor|\PHPUnit\Framework\MockObject\MockObject */
    protected $visitorMock;

    /** @var \Magento\Catalog\Model\Product\Compare\ListCompare|\PHPUnit\Framework\MockObject\MockObject */
    protected $listCompareMock;

    /** @var \Magento\Catalog\Model\Session|\PHPUnit\Framework\MockObject\MockObject */
    protected $catalogSession;

    /** @var \Magento\Store\Model\StoreManagerInterface|\PHPUnit\Framework\MockObject\MockObject */
    protected $storeManagerMock;

    /** @var \Magento\Framework\Data\Form\FormKey\Validator|\PHPUnit\Framework\MockObject\MockObject */
    protected $formKeyValidatorMock;

    /** @var \Magento\Framework\Controller\Result\RedirectFactory|\PHPUnit\Framework\MockObject\MockObject */
    protected $redirectFactoryMock;

    /** @var \Magento\Framework\View\Result\PageFactory|\PHPUnit\Framework\MockObject\MockObject */
    protected $pageFactoryMock;

    /** @var \Magento\Catalog\Api\ProductRepositoryInterface|\PHPUnit\Framework\MockObject\MockObject */
    protected $productRepositoryMock;

    /** @var \Magento\Framework\Url\DecoderInterface|\PHPUnit\Framework\MockObject\MockObject */
    protected $decoderMock;

    /** @var \Magento\Framework\App\RequestInterface|\PHPUnit\Framework\MockObject\MockObject */
    protected $request;

    /** @var \Magento\Framework\App\ResponseInterface|\PHPUnit\Framework\MockObject\MockObject */
    protected $response;

    protected function setUp(): void
    {
        $this->contextMock = $this->createPartialMock(
            \Magento\Framework\App\Action\Context::class,
            ['getRequest', 'getResponse', 'getResultRedirectFactory']
        );
        $this->request = $this->createMock(\Magento\Framework\App\RequestInterface::class);
        $this->response = $this->createMock(\Magento\Framework\App\ResponseInterface::class);
        $this->redirectFactoryMock = $this->createPartialMock(
            \Magento\Framework\Controller\Result\RedirectFactory::class,
            ['create']
        );
        $this->contextMock->expects($this->any())->method('getRequest')->willReturn($this->request);
        $this->contextMock->expects($this->any())->method('getResponse')->willReturn($this->response);
        $this->contextMock->expects($this->any())
            ->method('getResultRedirectFactory')
            ->willReturn($this->redirectFactoryMock);

        $this->itemFactoryMock = $this->createPartialMock(
            \Magento\Catalog\Model\Product\Compare\ItemFactory::class,
            ['create']
        );
        $this->collectionFactoryMock = $this->createPartialMock(
            \Magento\Catalog\Model\ResourceModel\Product\Compare\Item\CollectionFactory::class,
            ['create']
        );
        $this->sessionMock = $this->createMock(\Magento\Customer\Model\Session::class);
        $this->visitorMock = $this->createMock(\Magento\Customer\Model\Visitor::class);
        $this->listCompareMock = $this->createMock(\Magento\Catalog\Model\Product\Compare\ListCompare::class);
        $this->catalogSession = $this->createPartialMock(
            \Magento\Catalog\Model\Session::class,
            ['setBeforeCompareUrl']
        );
        $this->storeManagerMock = $this->createMock(\Magento\Store\Model\StoreManagerInterface::class);
        $this->formKeyValidatorMock = $this->getMockBuilder(\Magento\Framework\Data\Form\FormKey\Validator::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->pageFactoryMock = $this->createMock(\Magento\Framework\View\Result\PageFactory::class);
        $this->productRepositoryMock = $this->createMock(\Magento\Catalog\Api\ProductRepositoryInterface::class);
        $this->decoderMock = $this->createMock(\Magento\Framework\Url\DecoderInterface::class);

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
