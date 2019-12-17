<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Indexer\Test\Unit\Controller\Adminhtml\Indexer;

/**
 * Mass invalidate Test
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class MassInvalidateTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Indexer\Controller\Adminhtml\Indexer\MassInvalidate
     */
    protected $controller;

    /**
     * @var \Magento\Backend\App\Action\Context
     */
    protected $contextMock;

    /**
     * @var \Magento\Framework\App\ViewInterface
     */
    protected $view;

    /**
     * @var \Magento\Framework\View\Result\Page
     */
    protected $page;

    /**
     * @var \Magento\Framework\View\Page\Config
     */
    protected $config;

    /**
     * @var \Magento\Framework\View\Page\Title
     */
    protected $title;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $objectManager;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * @var \Magento\Framework\Indexer\IndexerRegistry
     */
    protected $indexReg;

    /**
     * @var \Magento\Framework\App\ResponseInterface
     */
    protected $response;

    /**
     * @var \Magento\Framework\App\ActionFlag
     */
    protected $actionFlag;

    /**
     * @var \Magento\Backend\Helper\Data
     */
    protected $helper;

    /**
     * @var  \Magento\Backend\Model\Session
     */
    protected $session;

    /**
     * @var \Magento\Framework\Controller\Result\Redirect
     */
    protected $resultRedirect;

    /**
     * Set up test
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function setUp()
    {
        $this->contextMock = $this->createPartialMock(
            \Magento\Backend\App\Action\Context::class,
            [
                'getAuthorization',
                'getSession',
                'getActionFlag',
                'getAuth',
                'getView',
                'getHelper',
                'getBackendUrl',
                'getFormKeyValidator',
                'getLocaleResolver',
                'getCanUseBaseUrl',
                'getRequest',
                'getResponse',
                'getObjectManager',
                'getMessageManager',
                'getResultRedirectFactory',
            ]
        );

        $this->response = $this->createPartialMock(
            \Magento\Framework\App\ResponseInterface::class,
            ['setRedirect', 'sendResponse']
        );

        $this->view = $this->createPartialMock(
            \Magento\Framework\App\ViewInterface::class,
            [
                'loadLayout',
                'getPage',
                'getConfig',
                'getTitle',
                'renderLayout',
                'loadLayoutUpdates',
                'getDefaultLayoutHandle',
                'addPageLayoutHandles',
                'generateLayoutBlocks',
                'generateLayoutXml',
                'getLayout',
                'addActionLayoutHandles',
                'setIsLayoutLoaded',
                'isLayoutLoaded'
            ]
        );

        $this->session = $this->createPartialMock(\Magento\Backend\Model\Session::class, ['setIsUrlNotice']);
        $this->session->expects($this->any())->method('setIsUrlNotice')->willReturn($this->objectManager);
        $this->actionFlag = $this->createPartialMock(\Magento\Framework\App\ActionFlag::class, ['get']);
        $this->actionFlag->expects($this->any())->method("get")->willReturn($this->objectManager);
        $this->objectManager = $this->createPartialMock(
            \Magento\Framework\TestFramework\Unit\Helper\ObjectManager::class,
            ['get']
        );
        $this->request = $this->getMockForAbstractClass(
            \Magento\Framework\App\RequestInterface::class,
            ['getParam', 'getRequest'],
            '',
            false
        );

        $resultRedirectFactory = $this->createPartialMock(
            \Magento\Backend\Model\View\Result\RedirectFactory::class,
            ['create']
        );
        $this->resultRedirect = $this->createPartialMock(
            \Magento\Framework\Controller\Result\Redirect::class,
            ['setPath']
        );
        $this->contextMock->expects($this->any())->method('getResultRedirectFactory')
            ->willReturn($resultRedirectFactory);
        $resultRedirectFactory->expects($this->any())->method('create')
            ->willReturn($this->resultRedirect);

        $this->response->expects($this->any())->method("setRedirect")->willReturn(1);
        $this->page = $this->createMock(\Magento\Framework\View\Result\Page::class);
        $this->config = $this->createMock(\Magento\Framework\View\Result\Page::class);
        $this->title = $this->createMock(\Magento\Framework\View\Page\Title::class);
        $this->messageManager = $this->getMockForAbstractClass(
            \Magento\Framework\Message\ManagerInterface::class,
            ['addError', 'addSuccess'],
            '',
            false
        );

        $this->indexReg = $this->createPartialMock(
            \Magento\Framework\Indexer\IndexerRegistry::class,
            ['get', 'setScheduled']
        );
        $this->helper = $this->createPartialMock(\Magento\Backend\Helper\Data::class, ['getUrl']);
        $this->contextMock->expects($this->any())->method("getObjectManager")->willReturn($this->objectManager);
        $this->contextMock->expects($this->any())->method("getRequest")->willReturn($this->request);
        $this->contextMock->expects($this->any())->method("getResponse")->willReturn($this->response);
        $this->contextMock->expects($this->any())->method("getMessageManager")->willReturn($this->messageManager);
        $this->contextMock->expects($this->any())->method("getSession")->willReturn($this->session);
        $this->contextMock->expects($this->any())->method("getActionFlag")->willReturn($this->actionFlag);
        $this->contextMock->expects($this->any())->method("getHelper")->willReturn($this->helper);
    }

    /**
     * @param array $indexerIds
     * @param \Exception $exception
     * @dataProvider executeDataProvider
     */
    public function testExecute($indexerIds, $exception)
    {
        $this->controller = new \Magento\Indexer\Controller\Adminhtml\Indexer\MassInvalidate(
            $this->contextMock,
            $this->indexReg
        );
        $this->request->expects($this->any())
            ->method('getParam')->with('indexer_ids')
            ->will($this->returnValue($indexerIds));

        if (!is_array($indexerIds)) {
            $this->messageManager->expects($this->once())
                ->method('addError')->with(__('Please select indexers.'))
                ->will($this->returnValue(1));
        } else {
            $indexerInterface = $this->getMockForAbstractClass(
                \Magento\Framework\Indexer\IndexerInterface::class,
                ['invalidate'],
                '',
                false
            );
            $this->indexReg->expects($this->any())
                ->method('get')->with(1)
                ->will($this->returnValue($indexerInterface));

            $indexerInterface->expects($this->any())
                ->method('invalidate')->with(true)
                ->will($this->returnValue(1));

            $this->messageManager->expects($this->any())
                ->method('addSuccess')
                ->will($this->returnValue(1));

            if ($exception) {
                $this->indexReg->expects($this->any())
                    ->method('get')->with(2)
                    ->will($this->throwException($exception));

                if ($exception instanceof \Magento\Framework\Exception\LocalizedException) {
                    $this->messageManager->expects($this->once())
                        ->method('addError')
                        ->with($exception->getMessage());
                } else {
                    $this->messageManager->expects($this->once())
                        ->method('addException')
                        ->with($exception, "We couldn't invalidate indexer(s) because of an error.");
                }
            }
        }

        $this->helper->expects($this->any())->method("getUrl")->willReturn("magento.com");
        $this->response->expects($this->any())->method("setRedirect")->willReturn(1);
        $this->resultRedirect->expects($this->once())->method('setPath')->with('*/*/list');

        $this->controller->execute();
    }

    /**
     * @return array
     */
    public function executeDataProvider()
    {
        return [
            'set1' => [
                'indexers' => 1,
                'exception' => null,
            ],
            'set2' => [
                'indexers' => [1],
                'exception' => null,
            ],
            'set3' => [
                'indexers' => [2],
                'exception' => new \Magento\Framework\Exception\LocalizedException(__('Test Phrase')),
            ],
            'set4' => [
                'indexers' => [2],
                'exception' => new \Exception(),
            ]
        ];
    }
}
