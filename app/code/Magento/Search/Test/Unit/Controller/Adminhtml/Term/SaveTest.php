<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Search\Test\Unit\Controller\Adminhtml\Term;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

class SaveTest extends \PHPUnit\Framework\TestCase
{
    /** @var \Magento\Framework\App\RequestInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $request;

    /** @var \Magento\Backend\Model\View\Result\Redirect|\PHPUnit\Framework\MockObject\MockObject */
    private $redirect;

    /** @var \Magento\Framework\Message\ManagerInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $messageManager;

    /** @var \Magento\Backend\Model\Session|\PHPUnit\Framework\MockObject\MockObject */
    private $session;

    /** @var \Magento\Backend\App\Action\Context|\PHPUnit\Framework\MockObject\MockObject */
    private $context;

    /** @var \Magento\Search\Model\Query|\PHPUnit\Framework\MockObject\MockObject */
    private $query;

    /** @var \Magento\Search\Controller\Adminhtml\Term\Save */
    private $controller;

    protected function setUp(): void
    {
        $objectManagerHelper = new ObjectManagerHelper($this);

        $this->context = $this->getMockBuilder(\Magento\Backend\App\Action\Context::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->redirect = $this->getMockBuilder(\Magento\Backend\Model\View\Result\Redirect::class)
            ->setMethods(['setPath'])
            ->disableOriginalConstructor()
            ->getMock();
        $redirectFactory = $this->getMockBuilder(\Magento\Framework\Controller\ResultFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $redirectFactory->expects($this->any())
            ->method('create')
            ->willReturn($this->redirect);
        $this->context->expects($this->any())
            ->method('getResultRedirectFactory')
            ->willReturn($redirectFactory);
        $this->context->expects($this->any())
            ->method('getResultFactory')
            ->willReturn($redirectFactory);

        $this->request = $this->getMockBuilder(\Magento\Framework\App\RequestInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getPostValue', 'isPost', 'getPost'])
            ->getMockForAbstractClass();
        $this->context->expects($this->atLeastOnce())
            ->method('getRequest')
            ->willReturn($this->request);

        $objectManager = $this->getMockBuilder(\Magento\Framework\ObjectManagerInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMockForAbstractClass();
        $this->context->expects($this->any())
            ->method('getObjectManager')
            ->willReturn($objectManager);

        $this->messageManager = $this->getMockBuilder(\Magento\Framework\Message\ManagerInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['addSuccessMessage', 'addErrorMessage', 'addExceptionMessage'])
            ->getMockForAbstractClass();
        $this->context->expects($this->any())
            ->method('getMessageManager')
            ->willReturn($this->messageManager);

        $this->session = $this->getMockBuilder(\Magento\Backend\Model\Session::class)
            ->disableOriginalConstructor()
            ->setMethods(['setPageData'])
            ->getMock();
        $this->context->expects($this->any())
            ->method('getSession')
            ->willReturn($this->session);

        $this->query = $this->getMockBuilder(\Magento\Search\Model\Query::class)
            ->disableOriginalConstructor()
            ->setMethods(['getId', 'load', 'addData', 'setIsProcessed', 'save', 'loadByQueryText', 'setStoreId'])
            ->getMock();
        $queryFactory = $this->getMockBuilder(\Magento\Search\Model\QueryFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $queryFactory->expects($this->any())
            ->method('create')
            ->willReturn($this->query);

        $this->controller = $objectManagerHelper->getObject(
            \Magento\Search\Controller\Adminhtml\Term\Save::class,
            [
                'context' => $this->context,
                'queryFactory' => $queryFactory,
            ]
        );
    }

    /**
     * @param bool $isPost
     * @param array $data
     * @dataProvider executeIsPostDataDataProvider
     */
    public function testExecuteIsPostData($isPost, $data)
    {
        $this->request->expects($this->at(0))->method('getPostValue')->willReturn($data);
        $this->request->expects($this->at(1))->method('isPost')->willReturn($isPost);
        $this->redirect->expects($this->once())->method('setPath')->willReturnSelf();
        $this->assertSame($this->redirect, $this->controller->execute());
    }

    /**
     * @return array
     */
    public function executeIsPostDataDataProvider()
    {
        return [
            [false, ['0' => '0']],
            [true, []]
        ];
    }

    public function testExecuteLoadQueryQueryId()
    {
        $queryId = 1;
        $queryText = '';
        $this->mockGetRequestData($queryText, $queryId);

        $this->query->expects($this->once())->method('getId')->willReturn(false);
        $this->query->expects($this->once())->method('load')->with($queryId);

        $this->messageManager->expects($this->once())->method('addSuccessMessage');

        $this->redirect->expects($this->once())->method('setPath')->willReturnSelf();
        $this->assertSame($this->redirect, $this->controller->execute());
    }

    public function testExecuteLoadQueryQueryIdQueryText()
    {
        $queryId = 1;
        $queryText = 'search';
        $this->mockGetRequestData($queryText, $queryId);

        $this->request->expects($this->at(4))->method('getPost')->with('store_id', false)->willReturn(1);

        $this->query->expects($this->once())->method('setStoreId');
        $this->query->expects($this->once())->method('loadByQueryText')->with($queryText);
        $this->query->expects($this->any())->method('getId')->willReturn($queryId);

        $this->messageManager->expects($this->once())->method('addSuccessMessage');

        $this->redirect->expects($this->once())->method('setPath')->willReturnSelf();
        $this->assertSame($this->redirect, $this->controller->execute());
    }

    public function testExecuteLoadQueryQueryIdQueryText2()
    {
        $queryId = 1;
        $queryText = 'search';
        $this->mockGetRequestData($queryText, $queryId);

        $this->request->expects($this->at(4))->method('getPost')->with('store_id', false)->willReturn(1);

        $this->query->expects($this->once())->method('setStoreId');
        $this->query->expects($this->once())->method('loadByQueryText')->with($queryText);
        $this->query->expects($this->any())->method('getId')->willReturn(false);
        $this->query->expects($this->once())->method('load')->with($queryId);

        $this->messageManager->expects($this->once())->method('addSuccessMessage');

        $this->redirect->expects($this->once())->method('setPath')->willReturnSelf();
        $this->assertSame($this->redirect, $this->controller->execute());
    }

    public function testExecuteLoadQueryQueryIdQueryTextException()
    {
        $queryId = 1;
        $anotherQueryId = 2;
        $queryText = 'search';
        $this->mockGetRequestData($queryText, $queryId);

        $this->request->expects($this->at(4))->method('getPost')->with('store_id', false)->willReturn(1);

        $this->query->expects($this->once())->method('setStoreId');
        $this->query->expects($this->once())->method('loadByQueryText')->with($queryText);
        $this->query->expects($this->any())->method('getId')->willReturn($anotherQueryId);

        $this->messageManager->expects($this->once())->method('addErrorMessage');
        $this->session->expects($this->once())->method('setPageData');
        $this->redirect->expects($this->once())->method('setPath')->willReturnSelf();
        $this->assertSame($this->redirect, $this->controller->execute());
    }

    public function testExecuteException()
    {
        $queryId = 1;
        $queryText = 'search';
        $this->mockGetRequestData($queryText, $queryId);

        $this->request->expects($this->at(4))->method('getPost')->with('store_id', false)->willReturn(1);

        $this->query->expects($this->once())->method('setStoreId');
        $this->query->expects($this->once())->method('loadByQueryText')->willThrowException(new \Exception());

        $this->messageManager->expects($this->once())->method('addExceptionMessage');
        $this->session->expects($this->once())->method('setPageData');
        $this->redirect->expects($this->once())->method('setPath')->willReturnSelf();
        $this->assertSame($this->redirect, $this->controller->execute());
    }

    /**
     * @param string $queryText
     * @param int $queryId
     */
    private function mockGetRequestData($queryText, $queryId)
    {
        $this->request->expects($this->at(0))->method('getPostValue')->willReturn(['0' => '0']);
        $this->request->expects($this->at(1))->method('isPost')->willReturn(true);
        $this->request->expects($this->at(2))->method('getPost')->with('query_text', false)->willReturn($queryText);
        $this->request->expects($this->at(3))->method('getPost')->with('query_id', null)->willReturn($queryId);
    }
}
