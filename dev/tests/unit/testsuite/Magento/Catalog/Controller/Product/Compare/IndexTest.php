<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Controller\Product\Compare;

use Magento\Catalog\Model\Resource\Product\Compare\Item;

class IndexTest extends \PHPUnit_Framework_TestCase
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

    /** @var \Magento\Core\App\Action\FormKeyValidator|\PHPUnit_Framework_MockObject_MockObject */
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
        $this->contextMock = $this->getMock('Magento\Framework\App\Action\Context',
            ['getRequest', 'getResponse'],
            [],
            '',
            false
        );
        $this->request = $this->getMock('Magento\Framework\App\RequestInterface', [], [], '', false);
        $this->response = $this->getMock('Magento\Framework\App\ResponseInterface', [], [], '', false);
        $this->contextMock->expects($this->any())
            ->method('getRequest')
            ->willReturn($this->request);
        $this->contextMock->expects($this->any())
            ->method('getResponse')
            ->willReturn($this->response);

        $this->itemFactoryMock = $this->getMock('Magento\Catalog\Model\Product\Compare\ItemFactory', [], [], '', false);
        $this->collectionFactoryMock = $this->getMock(
            'Magento\Catalog\Model\Resource\Product\Compare\Item\CollectionFactory',
            [],
            [],
            '',
            false
        );
        $this->sessionMock = $this->getMock('Magento\Customer\Model\Session', [], [], '', false);
        $this->visitorMock = $this->getMock('Magento\Customer\Model\Visitor', [], [], '', false);
        $this->listCompareMock = $this->getMock('Magento\Catalog\Model\Product\Compare\ListCompare', [], [], '', false);
        $this->catalogSession = $this->getMock('Magento\Catalog\Model\Session', ['setBeforeCompareUrl'], [], '', false);
        $this->storeManagerMock = $this->getMock('Magento\Store\Model\StoreManagerInterface');
        $this->formKeyValidatorMock = $this->getMock('Magento\Core\App\Action\FormKeyValidator', [], [], '', false);
        $this->redirectFactoryMock = $this->getMock(
            'Magento\Framework\Controller\Result\RedirectFactory',
            ['create'],
            [],
            '',
            false
        );
        $this->pageFactoryMock = $this->getMock('Magento\Framework\View\Result\PageFactory', [], [], '', false);
        $this->productRepositoryMock = $this->getMock('Magento\Catalog\Api\ProductRepositoryInterface');
        $this->decoderMock = $this->getMock('Magento\Framework\Url\DecoderInterface');

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
            $this->redirectFactoryMock,
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
            ->willReturnMap([
                    ['items', null, null],
                    ['uenc', null, $beforeUrl],
                ]);
        $this->decoderMock->expects($this->once())
            ->method('decode')
            ->with($beforeUrl)
            ->willReturn($beforeUrl . '1');
        $this->catalogSession->expects($this->once())
            ->method('setBeforeCompareUrl')
            ->with($beforeUrl . '1')
            ->willReturnSelf();
        $this->listCompareMock->expects($this->never())->method('addProducts');
        $this->redirectFactoryMock->expects($this->never())->method('create');
        $this->index->execute();
    }

    public function testExecuteWithItems()
    {
        $this->request->expects($this->any())
            ->method('getParam')
            ->willReturnMap([
                    ['items', null, '1,2,3'],
                    ['uenc', null, null],
                ]);
        $this->decoderMock->expects($this->never())->method('decode');
        $this->catalogSession->expects($this->never())->method('setBeforeCompareUrl');

        $this->listCompareMock->expects($this->once())
            ->method('addProducts')
            ->with([1, 2, 3]);
        $redirect = $this->getMock('Magento\Framework\Controller\Result\Redirect', ['setPath'], [], '', false);
        $redirect->expects($this->once())
            ->method('setPath')
            ->with('*/*/*');
        $this->redirectFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($redirect);
        $this->index->execute();
    }
}
