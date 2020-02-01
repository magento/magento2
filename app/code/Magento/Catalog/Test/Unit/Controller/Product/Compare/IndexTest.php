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

    /** @var \Magento\Framework\App\Action\Context|\PHPUnit_Framework_MockObject_MockObject */
    protected $contextMock;

    /** @var \Magento\Catalog\Model\Product\Compare\ItemFactory|\PHPUnit_Framework_MockObject_MockObject */
    protected $itemFactoryMock;

    /** @var Item\CollectionFactory|\PHPUnit_Framework_MockObject_MockObject */
    protected $collectionFactoryMock;

    /** @var \Magento\Customer\Model\Session|\PHPUnit_Framework_MockObject_MockObject */
    protected $sessionMock;

    /** @var \Magento\Customer\Model\Visitor|\PHPUnit_Framework_MockObject_MockObject */
    protected $visitorMock;

    /** @var \Magento\Catalog\Model\Product\Compare\ListCompare|\PHPUnit_Framework_MockObject_MockObject */
    protected $listCompareMock;

    /** @var \Magento\Catalog\Model\Session|\PHPUnit_Framework_MockObject_MockObject */
    protected $catalogSession;

    /** @var \Magento\Store\Model\StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $storeManagerMock;

    /** @var \Magento\Framework\Data\Form\FormKey\Validator|\PHPUnit_Framework_MockObject_MockObject */
    protected $formKeyValidatorMock;

    /** @var \Magento\Framework\Controller\Result\RedirectFactory|\PHPUnit_Framework_MockObject_MockObject */
    protected $redirectFactoryMock;

    /** @var \Magento\Framework\View\Result\PageFactory|\PHPUnit_Framework_MockObject_MockObject */
    protected $pageFactoryMock;

    /** @var \Magento\Catalog\Api\ProductRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $productRepositoryMock;

    /** @var \Magento\Framework\Url\DecoderInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $decoderMock;

    /** @var \Magento\Framework\App\RequestInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $request;

    /** @var \Magento\Framework\App\ResponseInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $response;

    protected function setUp()
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
