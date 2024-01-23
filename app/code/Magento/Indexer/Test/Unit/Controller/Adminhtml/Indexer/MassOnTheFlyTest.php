<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Indexer\Test\Unit\Controller\Adminhtml\Indexer;

use Magento\Backend\App\Action\Context;
use Magento\Backend\Helper\Data;
use Magento\Backend\Model\Session;
use Magento\Framework\App\ActionFlag;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\ViewInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Indexer\IndexerInterface;
use Magento\Framework\Indexer\IndexerRegistry;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Page\Config;
use Magento\Framework\View\Page\Title;
use Magento\Framework\View\Result\Page;
use Magento\Indexer\Controller\Adminhtml\Indexer\MassOnTheFly;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class MassOnTheFlyTest extends TestCase
{
    /**
     * @var MassOnTheFly
     */
    protected $model;

    /**
     * @var /Magento\Backend\App\Action\Context
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
     * Set up test
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function setUp(): void
    {
        $this->contextMock = $this->createPartialMock(Context::class, [
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
            'getMessageManager'
        ]);

        $this->response = $this->getMockBuilder(ResponseInterface::class)
            ->addMethods(['setRedirect'])
            ->onlyMethods(['sendResponse'])
            ->getMockForAbstractClass();

        $this->view = $this->getMockBuilder(ViewInterface::class)
            ->addMethods(['getConfig', 'getTitle'])
            ->onlyMethods([
                'loadLayout',
                'getPage',
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
            ])
            ->getMockForAbstractClass();

        $this->session = $this->getMockBuilder(Session::class)
            ->addMethods(['setIsUrlNotice'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->session->expects($this->any())->method('setIsUrlNotice')->willReturn($this->objectManager);
        $this->actionFlag = $this->createPartialMock(ActionFlag::class, ['get']);
        $this->actionFlag->expects($this->any())->method("get")->willReturn($this->objectManager);
        $this->objectManager = $this->getMockBuilder(ObjectManager::class)
            ->addMethods(['get'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->request = $this->getMockForAbstractClass(
            RequestInterface::class,
            ['getParam', 'getRequest'],
            '',
            false
        );

        $this->response->expects($this->any())->method("setRedirect")->willReturn(1);
        $this->page = $this->createMock(Page::class);
        $this->config = $this->createMock(Page::class);
        $this->title = $this->createMock(Title::class);
        $this->messageManager = $this->getMockForAbstractClass(
            ManagerInterface::class,
            ['addErrorMessage', 'addSuccess'],
            '',
            false
        );

        $this->indexReg = $this->getMockBuilder(IndexerRegistry::class)
            ->addMethods(['setScheduled'])
            ->onlyMethods(['get'])
            ->disableOriginalConstructor()
            ->getMock();
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
     * @param array $expectsExceptionValues
     * @dataProvider executeDataProvider
     */
    public function testExecute($indexerIds, $exception, $expectsExceptionValues)
    {
        $this->model = new MassOnTheFly($this->contextMock);
        $this->request->expects($this->any())
            ->method('getParam')->with('indexer_ids')
            ->willReturn($indexerIds);

        if (!is_array($indexerIds)) {
            $this->messageManager->expects($this->once())
                ->method('addErrorMessage')->with(__('Please select indexers.'))
                ->willReturn(1);
        } else {
            $this->objectManager->expects($this->any())
                ->method('get')->with(IndexerRegistry::class)
                ->willReturn($this->indexReg);
            $indexerInterface = $this->getMockForAbstractClass(
                IndexerInterface::class,
                ['setScheduled'],
                '',
                false
            );
            $this->indexReg->expects($this->any())
                ->method('get')->with(1)
                ->willReturn($indexerInterface);

            if ($exception !== null) {
                $indexerInterface->expects($this->any())
                    ->method('isScheduled')->willReturn(true);
                $indexerInterface->expects($this->any())
                    ->method('setScheduled')->with(false)->willThrowException($exception);
            } else {
                $indexerInterface->expects($this->any())
                    ->method('setScheduled')->with(false)->willReturn(1);
            }

            $this->messageManager->expects($this->any())->method('addSuccess')->willReturn(1);

            if ($exception !== null) {
                $this->messageManager->expects($this->exactly($expectsExceptionValues[2]))
                    ->method('addErrorMessage')
                    ->with($exception->getMessage());
                $this->messageManager->expects($this->exactly($expectsExceptionValues[1]))
                    ->method('addException')
                    ->with($exception, "We couldn't change indexer(s)' mode because of an error.");
            }
        }

        $this->helper->expects($this->any())->method("getUrl")->willReturn("magento.com");
        $this->response->expects($this->any())->method("setRedirect")->willReturn(1);

        $result = $this->model->execute();
        $this->assertNull($result);
    }

    /**
     * @return array
     */
    public function executeDataProvider()
    {
        return [
            'set1' => [
                'idexers' => 1,
                "exception" => null,
                "expectsValues" => [0, 0, 0]
            ],
            'set2' => [
                'idexers' => [1],
                "exception" => null,
                "expectsException" => [1, 0, 0]
            ],
            'set3' => [
                'idexers' => [1],
                "exception" => new LocalizedException(__('Test Phrase')),
                "expectsException" => [0, 0, 1]
            ],
            'set4' => [
                'idexers' => [1],
                "exception" => new \Exception(),
                "expectsException" => [0, 1, 0]
            ]
        ];
    }
}
