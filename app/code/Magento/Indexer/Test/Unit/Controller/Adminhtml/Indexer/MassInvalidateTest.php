<?php declare(strict_types=1);
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Indexer\Test\Unit\Controller\Adminhtml\Indexer;

use Magento\Backend\App\Action\Context;
use Magento\Backend\Helper\Data;
use Magento\Backend\Model\Session;
use Magento\Backend\Model\View\Result\RedirectFactory;
use Magento\Framework\App\ActionFlag;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\ViewInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Indexer\IndexerInterface;
use Magento\Framework\Indexer\IndexerRegistry;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Page\Config;
use Magento\Framework\View\Page\Title;
use Magento\Framework\View\Result\Page;
use Magento\Indexer\Controller\Adminhtml\Indexer\MassInvalidate;
use PHPUnit\Framework\TestCase;

/**
 * Test for Mass invalidate action
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class MassInvalidateTest extends TestCase
{
    /**
     * @var MassInvalidate
     */
    protected $controller;

    /**
     * @var Context
     */
    protected $contextMock;

    /**
     * @var ViewInterface
     */
    protected $view;

    /**
     * @var Page
     */
    protected $page;

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var Title
     */
    protected $title;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @var ManagerInterface
     */
    protected $messageManager;

    /**
     * @var IndexerRegistry
     */
    protected $indexReg;

    /**
     * @var ResponseInterface
     */
    protected $response;

    /**
     * @var ActionFlag
     */
    protected $actionFlag;

    /**
     * @var Data
     */
    protected $helper;

    /**
     * @var  Session
     */
    protected $session;

    /**
     * @var Redirect
     */
    protected $resultRedirect;

    /**
     * Set up test
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function setUp(): void
    {
        $this->contextMock = $this->createPartialMock(
            Context::class,
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
            ResponseInterface::class,
            ['setRedirect', 'sendResponse']
        );

        $this->view = $this->createPartialMock(
            ViewInterface::class,
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

        $this->session = $this->createPartialMock(Session::class, ['setIsUrlNotice']);
        $this->session->expects($this->any())->method('setIsUrlNotice')->willReturn($this->objectManager);
        $this->actionFlag = $this->createPartialMock(ActionFlag::class, ['get']);
        $this->actionFlag->expects($this->any())->method("get")->willReturn($this->objectManager);
        $this->objectManager = $this->createPartialMock(
            ObjectManager::class,
            ['get']
        );
        $this->request = $this->getMockForAbstractClass(
            RequestInterface::class,
            ['getParam', 'getRequest'],
            '',
            false
        );

        $resultRedirectFactory = $this->createPartialMock(
            RedirectFactory::class,
            ['create']
        );
        $this->resultRedirect = $this->createPartialMock(
            Redirect::class,
            ['setPath']
        );
        $this->contextMock->expects($this->any())->method('getResultRedirectFactory')
            ->willReturn($resultRedirectFactory);
        $resultRedirectFactory->expects($this->any())->method('create')
            ->willReturn($this->resultRedirect);

        $this->response->expects($this->any())->method("setRedirect")->willReturn(1);
        $this->page = $this->createMock(Page::class);
        $this->config = $this->createMock(Page::class);
        $this->title = $this->createMock(Title::class);
        $this->messageManager = $this->getMockForAbstractClass(
            ManagerInterface::class,
            ['addError', 'addSuccess'],
            '',
            false
        );

        $this->indexReg = $this->createPartialMock(
            IndexerRegistry::class,
            ['get', 'setScheduled']
        );
        $this->helper = $this->createPartialMock(Data::class, ['getUrl']);
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
        $this->controller = new MassInvalidate(
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
                IndexerInterface::class,
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

                if ($exception instanceof LocalizedException) {
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
                'exception' => new LocalizedException(__('Test Phrase')),
            ],
            'set4' => [
                'indexers' => [2],
                'exception' => new \Exception(),
            ]
        ];
    }
}
