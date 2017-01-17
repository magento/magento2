<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Controller\Adminhtml\Guarantee;

use Magento\Framework\Message\MessageInterface;
use Magento\Signifyd\Api\CaseRepositoryInterface;
use Magento\Signifyd\Api\GuaranteeCancelingServiceInterface;
use Magento\Signifyd\Model\Guarantee\CancelingService;
use Magento\TestFramework\TestCase\AbstractBackendController;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

class CancelTest extends AbstractBackendController
{
    /**
     * @var string
     */
    private static $entryPoint = 'backend/signifyd/guarantee/cancel';

    /**
     * @var GuaranteeCancelingServiceInterface|MockObject
     */
    private $cancelingService;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        parent::setUp();

        $this->cancelingService = $this->getMockBuilder(GuaranteeCancelingServiceInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['cancelForOrder'])
            ->getMockForAbstractClass();

        $this->_objectManager->addSharedInstance($this->cancelingService, CancelingService::class);
    }

    /**
     * @inheritdoc
     */
    protected function tearDown()
    {
        $this->_objectManager->removeSharedInstance(CancelingService::class);

        parent::tearDown();
    }

    /**
     * Checks a test case, when order id is missed in request.
     *
     * @covers \Magento\Signifyd\Controller\Adminhtml\Guarantee\Cancel::execute
     * @magentoAppArea adminhtml
     */
    public function testExecuteWithEmptyOrderId()
    {
        $this->getRequest()->setPostValue('order_id', null);

        $this->cancelingService->expects(self::never())
            ->method('cancelForOrder');

        $this->dispatch(self::$entryPoint);

        self::assertRedirect(self::stringContains('backend/sales/order/index'));
        self::assertSessionMessages(
            self::equalTo(['Order id is required.']),
            MessageInterface::TYPE_ERROR
        );
    }

    /**
     * Checks a test case, when guarantee is not available for canceling.
     *
     * @covers \Magento\Signifyd\Controller\Adminhtml\Guarantee\Cancel::execute
     * @magentoAppArea adminhtml
     */
    public function testExecuteWithNotAvailableGuarantee()
    {
        $this->getRequest()->setPostValue('order_id', 123);

        $this->cancelingService->expects(self::never())
            ->method('cancelForOrder');

        $this->dispatch(self::$entryPoint);

        self::assertRedirect(self::stringContains('backend/sales/order/view'));
        self::assertSessionMessages(
            self::equalTo(['Sorry, we cannot cancel Guarantee for order.']),
            MessageInterface::TYPE_ERROR
        );
    }

    /**
     * Checks as test case, when canceling service cannot successfully cancel guarantee.
     *
     * @covers \Magento\Signifyd\Controller\Adminhtml\Guarantee\Cancel::execute
     * @magentoDataFixture Magento/Signifyd/_files/approved_case.php
     * @magentoAppArea adminhtml
     */
    public function testExecuteWithCancelingFailedRequest()
    {
        $caseId = 123;

        /** @var CaseRepositoryInterface $caseRepository */
        $caseRepository = $this->_objectManager->get(CaseRepositoryInterface::class);
        $caseEntity = $caseRepository->getByCaseId($caseId);

        $this->getRequest()->setPostValue('order_id', $caseEntity->getOrderId());

        $this->cancelingService->expects(self::once())
            ->method('cancelForOrder')
            ->with(self::equalTo($caseEntity->getOrderId()))
            ->willReturn(false);

        $this->dispatch(self::$entryPoint);

        self::assertRedirect(self::stringContains('backend/sales/order/view'));
        self::assertSessionMessages(
            self::equalTo(['Sorry, we cannot cancel Guarantee for order.']),
            MessageInterface::TYPE_ERROR
        );
    }

    /**
     * Checks a test case, when guarantee is successfully canceled for order.
     *
     * @covers \Magento\Signifyd\Controller\Adminhtml\Guarantee\Cancel::execute
     * @magentoDataFixture Magento/Signifyd/_files/approved_case.php
     * @magentoAppArea adminhtml
     */
    public function testExecute()
    {
        $caseId = 123;

        /** @var CaseRepositoryInterface $caseRepository */
        $caseRepository = $this->_objectManager->get(CaseRepositoryInterface::class);
        $caseEntity = $caseRepository->getByCaseId($caseId);

        $this->getRequest()->setPostValue('order_id', $caseEntity->getOrderId());

        $this->cancelingService->expects(self::once())
            ->method('cancelForOrder')
            ->with(self::equalTo($caseEntity->getOrderId()))
            ->willReturn(true);

        $this->dispatch(self::$entryPoint);

        self::assertRedirect(self::stringContains('backend/sales/order/view'));
        self::assertSessionMessages(
            self::equalTo(['Guarantee has been cancelled for order.']),
            MessageInterface::TYPE_SUCCESS
        );
    }
}
