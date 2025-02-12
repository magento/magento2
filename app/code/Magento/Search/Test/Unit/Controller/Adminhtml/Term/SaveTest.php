<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Search\Test\Unit\Controller\Adminhtml\Term;

use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\Session;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Search\Controller\Adminhtml\Term\Save;
use Magento\Search\Model\Query;
use Magento\Search\Model\QueryFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SaveTest extends TestCase
{
    /**
     * @var RequestInterface|MockObject
     */
    private $request;

    /**
     * @var Redirect|MockObject
     */
    private $redirect;

    /**
     * @var ManagerInterface|MockObject
     */
    private $messageManager;

    /**
     * @var Session|MockObject
     */
    private $session;

    /**
     * @var Context|MockObject
     */
    private $context;

    /**
     * @var Query|MockObject
     */
    private $query;

    /**
     * @var Save
     */
    private $controller;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $objectManagerHelper = new ObjectManagerHelper($this);

        $this->context = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->redirect = $this->getMockBuilder(Redirect::class)
            ->onlyMethods(['setPath'])
            ->disableOriginalConstructor()
            ->getMock();
        $redirectFactory = $this->getMockBuilder(ResultFactory::class)
            ->onlyMethods(['create'])
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

        $this->request = $this->getMockBuilder(RequestInterface::class)
            ->disableOriginalConstructor()
            ->addMethods(['getPostValue', 'isPost', 'getPost'])
            ->getMockForAbstractClass();
        $this->context->expects($this->atLeastOnce())
            ->method('getRequest')
            ->willReturn($this->request);

        $objectManager = $this->getMockBuilder(ObjectManagerInterface::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMockForAbstractClass();
        $this->context->expects($this->any())
            ->method('getObjectManager')
            ->willReturn($objectManager);

        $this->messageManager = $this->getMockBuilder(ManagerInterface::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['addSuccessMessage', 'addErrorMessage', 'addExceptionMessage'])
            ->getMockForAbstractClass();
        $this->context->expects($this->any())
            ->method('getMessageManager')
            ->willReturn($this->messageManager);

        $this->session = $this->getMockBuilder(Session::class)
            ->disableOriginalConstructor()
            ->addMethods(['setPageData'])
            ->getMock();
        $this->context->expects($this->any())
            ->method('getSession')
            ->willReturn($this->session);

        $this->query = $this->getMockBuilder(Query::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getId', 'load', 'addData', 'save', 'loadByQueryText', 'setStoreId'])
            ->addMethods(['setIsProcessed'])
            ->getMock();
        $queryFactory = $this->getMockBuilder(QueryFactory::class)
            ->onlyMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $queryFactory->expects($this->any())
            ->method('create')
            ->willReturn($this->query);

        $this->controller = $objectManagerHelper->getObject(
            Save::class,
            [
                'context' => $this->context,
                'queryFactory' => $queryFactory,
            ]
        );
    }

    /**
     * @param bool $isPost
     * @param array $data
     *
     * @return void
     * @dataProvider executeIsPostDataDataProvider
     */
    public function testExecuteIsPostData(bool $isPost, array $data): void
    {
        $this->request
            ->method('getPostValue')
            ->willReturn($data);
        $this->request
            ->method('isPost')
            ->willReturn($isPost);
        $this->redirect->expects($this->once())->method('setPath')->willReturnSelf();
        $this->assertSame($this->redirect, $this->controller->execute());
    }

    /**
     * @return array
     */
    public static function executeIsPostDataDataProvider(): array
    {
        return [
            [false, ['0' => '0']],
            [true, []]
        ];
    }

    /**
     * @return void
     */
    public function testExecuteLoadQueryQueryId(): void
    {
        $queryId = 1;
        $queryText = '';
        $this->mockGetRequestData($queryText, $queryId, false);

        $this->query->expects($this->once())->method('getId')->willReturn(false);
        $this->query->expects($this->once())->method('load')->with($queryId);

        $this->messageManager->expects($this->once())->method('addSuccessMessage');

        $this->redirect->expects($this->once())->method('setPath')->willReturnSelf();
        $this->assertSame($this->redirect, $this->controller->execute());
    }

    /**
     * @return void
     */
    public function testExecuteLoadQueryQueryIdQueryText(): void
    {
        $queryId = 1;
        $queryText = 'search';
        $this->mockGetRequestData($queryText, $queryId);

        $this->query->expects($this->once())->method('setStoreId');
        $this->query->expects($this->once())->method('loadByQueryText')->with($queryText);
        $this->query->expects($this->any())->method('getId')->willReturn($queryId);

        $this->messageManager->expects($this->once())->method('addSuccessMessage');

        $this->redirect->expects($this->once())->method('setPath')->willReturnSelf();
        $this->assertSame($this->redirect, $this->controller->execute());
    }

    /**
     * @return void
     */
    public function testExecuteLoadQueryQueryIdQueryText2(): void
    {
        $queryId = 1;
        $queryText = 'search';
        $this->mockGetRequestData($queryText, $queryId);

        $this->query->expects($this->once())->method('setStoreId');
        $this->query->expects($this->once())->method('loadByQueryText')->with($queryText);
        $this->query->expects($this->any())->method('getId')->willReturn(false);
        $this->query->expects($this->once())->method('load')->with($queryId);

        $this->messageManager->expects($this->once())->method('addSuccessMessage');

        $this->redirect->expects($this->once())->method('setPath')->willReturnSelf();
        $this->assertSame($this->redirect, $this->controller->execute());
    }

    /**
     * @return void
     */
    public function testExecuteLoadQueryQueryIdQueryTextException(): void
    {
        $queryId = 1;
        $anotherQueryId = 2;
        $queryText = 'search';
        $this->mockGetRequestData($queryText, $queryId);

        $this->query->expects($this->once())->method('setStoreId');
        $this->query->expects($this->once())->method('loadByQueryText')->with($queryText);
        $this->query->expects($this->any())->method('getId')->willReturn($anotherQueryId);

        $this->messageManager->expects($this->once())->method('addErrorMessage');
        $this->session->expects($this->once())->method('setPageData');
        $this->redirect->expects($this->once())->method('setPath')->willReturnSelf();
        $this->assertSame($this->redirect, $this->controller->execute());
    }

    /**
     * @return void
     */
    public function testExecuteException(): void
    {
        $queryId = 1;
        $queryText = 'search';
        $this->mockGetRequestData($queryText, $queryId);

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
     * @param bool $withStoreId
     *
     * @return void
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    private function mockGetRequestData(
        string $queryText,
        int $queryId,
        bool $withStoreId = true
    ): void {
        $this->request
            ->method('getPostValue')
            ->willReturn(['0' => '0']);
        $this->request
            ->method('isPost')
            ->willReturn(true);
        if ($withStoreId) {
            $this->request
                ->method('getPost')
                ->willReturnCallback(function ($arg1, $arg2) use ($queryText, $queryId) {
                    if ($arg1 == 'query_text' && $arg2 == false) {
                        return $queryText;
                    } elseif ($arg1 == 'query_id' && $arg2 == null) {
                        return $queryId;
                    } elseif ($arg1 == 'store_id' && $arg2 == false) {
                        return 1;
                    }
                });
        } else {
            $this->request
                ->method('getPost')
                ->willReturnCallback(function ($arg1, $arg2) use ($queryText, $queryId) {
                    if ($arg1 == 'query_text' && $arg2 == false) {
                        return $queryText;
                    } elseif ($arg1 == 'query_id' && $arg2 == null) {
                        return $queryId;
                    }
                });
        }
    }
}
