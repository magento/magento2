<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Controller\Adminhtml\Creditmemo\AbstractCreditmemo;

use Magento\Backend\App\Action\Context;
use Magento\Backend\Helper\Data;
use Magento\Backend\Model\Session;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Backend\Model\View\Result\RedirectFactory;
use Magento\Framework\App\ActionFlag;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Message\Manager;
use Magento\Framework\ObjectManager\ObjectManager as FrameworkObjectManager;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Sales\Api\CreditmemoManagementInterface;
use Magento\Sales\Controller\Adminhtml\Creditmemo\AbstractCreditmemo\Email;
use Magento\Sales\Api\CreditmemoRepositoryInterface;
use Magento\Sales\Model\Order\Creditmemo;
use Magento\Store\Model\Store;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class EmailTest extends TestCase
{
    use MockCreationTrait;

    /**
     * @var Email
     */
    protected $creditmemoEmail;

    /**
     * @var Context|MockObject
     */
    protected $context;

    /**
     * @var Http|MockObject
     */
    protected $request;

    /**
     * @var ResponseInterface|MockObject
     */
    protected $response;

    /**
     * @var Manager|MockObject
     */
    protected $messageManager;

    /**
     * @var \Magento\Framework\ObjectManager\ObjectManager|MockObject
     */
    protected $objectManager;

    /**
     * @var Session|MockObject
     */
    protected $session;

    /**
     * @var ActionFlag|MockObject
     */
    protected $actionFlag;

    /**
     * @var Data|MockObject
     */
    protected $helper;

    /**
     * @var RedirectFactory|MockObject
     */
    protected $resultRedirectFactoryMock;

    /**
     * @var Redirect|MockObject
     */
    protected $resultRedirectMock;

    /**
     * Test setup
     */
    protected function setUp(): void
    {
        $objectManagerHelper = new ObjectManagerHelper($this);
        $this->context = $this->createPartialMock(Context::class, [
            'getRequest',
            'getResponse',
            'getMessageManager',
            'getRedirect',
            'getObjectManager',
            'getSession',
            'getActionFlag',
            'getHelper',
            'getResultRedirectFactory'
        ]);
        $this->response = $this->createPartialMockWithReflection(
            ResponseInterface::class,
            ['setRedirect', 'sendResponse']
        );

        $this->request = $this->createMock(Http::class);
        $this->objectManager = $this->createPartialMock(
            FrameworkObjectManager::class,
            ['create']
        );
        $this->messageManager = $this->createPartialMock(
            Manager::class,
            ['addSuccessMessage', 'addWarningMessage']
        );
        $this->session = $this->createPartialMockWithReflection(Session::class, ['setIsUrlNotice']);
        $this->actionFlag = $this->createPartialMock(ActionFlag::class, ['get']);
        $this->helper = $this->createPartialMock(Data::class, ['getUrl']);
        $this->resultRedirectFactoryMock = $this->getMockBuilder(
            RedirectFactory::class
        )->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();
        $this->resultRedirectMock = $this->createMock(Redirect::class);
        $this->context->expects($this->once())->method('getMessageManager')->willReturn($this->messageManager);
        $this->context->expects($this->once())->method('getRequest')->willReturn($this->request);
        $this->context->expects($this->once())->method('getResponse')->willReturn($this->response);
        $this->context->expects($this->once())->method('getObjectManager')->willReturn($this->objectManager);
        $this->context->expects($this->once())->method('getSession')->willReturn($this->session);
        $this->context->expects($this->once())->method('getActionFlag')->willReturn($this->actionFlag);
        $this->context->expects($this->once())->method('getHelper')->willReturn($this->helper);
        $this->context->expects($this->once())
            ->method('getResultRedirectFactory')
            ->willReturn($this->resultRedirectFactoryMock);
        $this->creditmemoEmail = $objectManagerHelper->getObject(
            Email::class,
            [
                'context' => $this->context
            ]
        );
    }

    /**
     * testEmail
     */
    public function testEmail()
    {
        $cmId = 10000031;
        $cmManagement = CreditmemoManagementInterface::class;
        $cmManagementMock = $this->createMock($cmManagement);

        $creditmemoRepository = $this->createMock(CreditmemoRepositoryInterface::class);
        $creditmemo = $this->createMock(Creditmemo::class);
        $store = $this->createMock(Store::class);
        $store->expects($this->once())
            ->method('getConfig')
            ->with('sales_email/creditmemo/enabled')
            ->willReturn(true);
        $creditmemo->expects($this->once())
            ->method('getStore')
            ->willReturn($store);
        $creditmemoRepository->expects($this->once())
            ->method('get')
            ->with($cmId)
            ->willReturn($creditmemo);

        $this->prepareRedirect($cmId);

        $this->request->expects($this->once())
            ->method('getParam')
            ->with('creditmemo_id')
            ->willReturn($cmId);

        $this->objectManager->expects($this->exactly(2))
            ->method('create')
            ->willReturnCallback(fn($param) => match ($param) {
                CreditmemoRepositoryInterface::class => $creditmemoRepository,
                $cmManagement => $cmManagementMock,
                default => throw new \Exception("Unexpected create() parameter: $param")
            });

        $cmManagementMock->expects($this->once())
            ->method('notify')
            ->with($cmId)
            ->willReturn(true);
        
        $this->messageManager->expects($this->once())
            ->method('addSuccessMessage')
            ->with('You sent the message.');

        $this->assertInstanceOf(
            Redirect::class,
            $this->creditmemoEmail->execute()
        );
        $this->assertEquals($this->response, $this->creditmemoEmail->getResponse());
    }

    /**
     * testEmailDisabled
     */
    public function testEmailDisabled()
    {
        $cmId = 10000031;

        $creditmemoRepository = $this->createMock(CreditmemoRepositoryInterface::class);

        $creditmemo = $this->createMock(Creditmemo::class);

        $store = $this->createMock(Store::class);
        $store->expects($this->once())
            ->method('getConfig')
            ->with('sales_email/creditmemo/enabled')
            ->willReturn(false);

        $creditmemo->expects($this->once())
            ->method('getStore')
            ->willReturn($store);

        $creditmemoRepository->expects($this->once())
            ->method('get')
            ->with($cmId)
            ->willReturn($creditmemo);

        $this->prepareRedirect($cmId);

        $this->request->expects($this->once())
            ->method('getParam')
            ->with('creditmemo_id')
            ->willReturn($cmId);

        $this->objectManager->expects($this->once())
            ->method('create')
            ->willReturnCallback(fn($param) => match ($param) {
                CreditmemoRepositoryInterface::class => $creditmemoRepository,
                default => null
            });

        $this->messageManager->expects($this->once())
            ->method('addWarningMessage')
            ->with('Credit memo emails are disabled for this store. No email was sent.');

        $this->assertInstanceOf(
            Redirect::class,
            $this->creditmemoEmail->execute()
        );
    }

    /**
     * testEmailNoCreditmemoId
     */
    public function testEmailNoCreditmemoId()
    {
        $this->request->expects($this->once())
            ->method('getParam')
            ->with('creditmemo_id')
            ->willReturn(null);

        $this->assertNull($this->creditmemoEmail->execute());
    }

    /**
     * @param int $cmId
     */
    protected function prepareRedirect($cmId)
    {
        $this->resultRedirectFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->resultRedirectMock);
        $this->resultRedirectMock->expects($this->once())
            ->method('setPath')
            ->with('sales/order_creditmemo/view', ['creditmemo_id' => $cmId])
            ->willReturnSelf();
    }
}
