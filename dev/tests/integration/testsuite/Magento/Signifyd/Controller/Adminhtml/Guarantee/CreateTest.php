<?php

/**
 * Copyright © 2013-2017 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Controller\Adminhtml\Guarantee;

use Magento\TestFramework\TestCase\AbstractBackendController;
use Magento\Signifyd\Api\CaseRepositoryInterface;
use Magento\Signifyd\Api\Data\CaseInterface;
use Magento\Signifyd\Model\Guarantee\CreationService;
use Magento\Framework\Message\MessageInterface;

/**
 * Class tests creation Guarantee flow for order.
 */
class CreateTest extends AbstractBackendController
{
    /**
     * @var string
     */
    private static $entryPoint = 'backend/signifyd/guarantee/create';

    /**
     * @var CreationService|\PHPUnit_Framework_MockObject_MockObject
     */
    private $creationService;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        parent::setUp();

        $this->creationService = $this->getMockBuilder(CreationService::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->_objectManager->addSharedInstance($this->creationService, CreationService::class);
    }

    /**
     * Tests successful Guarantee creation for an order.
     *
     * @covers \Magento\Signifyd\Controller\Adminhtml\Guarantee\Create::execute
     * @magentoDataFixture Magento/Signifyd/_files/case.php
     * @magentoAppArea adminhtml
     */
    public function testExecuteSuccess()
    {
        $orderId = $this->getOrderId();
        $this->getRequest()->setPostValue('orderId', $orderId);

        $this->creationService->expects($this->once())
            ->method('create')
            ->with($orderId)
            ->willReturn(true);

        $this->dispatch(self::$entryPoint);

        $this->assertRedirect($this->stringContains('backend/sales/order/view'));
        $this->assertSessionMessages(
            $this->equalTo(['Order has been submitted for Guarantee.']),
            MessageInterface::TYPE_SUCCESS
        );
    }

    /**
     * Tests failure Guarantee creation due to empty order id.
     *
     * @covers \Magento\Signifyd\Controller\Adminhtml\Guarantee\Create::execute
     * @magentoDataFixture Magento/Signifyd/_files/case.php
     * @magentoAppArea adminhtml
     */
    public function testExecuteWithEmptyOrderId()
    {
        $orderId = '';
        $this->getRequest()->setPostValue('orderId', $orderId);

        $this->creationService->expects($this->never())
            ->method('create');

        $this->dispatch(self::$entryPoint);

        $this->assertRedirect($this->stringContains('backend/sales/order/index'));
        $this->assertSessionMessages(
            $this->equalTo(['Order id is required.']),
            MessageInterface::TYPE_ERROR
        );
    }

    /**
     * Tests failure Guarantee creation due to unsuccessful CreationService call.
     *
     * @covers \Magento\Signifyd\Controller\Adminhtml\Guarantee\Create::execute
     * @magentoDataFixture Magento/Signifyd/_files/case.php
     * @magentoAppArea adminhtml
     */
    public function testExecuteWithCreationServiceFail()
    {
        $orderId = $this->getOrderId();
        $this->getRequest()->setPostValue('orderId', $orderId);

        $this->creationService->expects($this->once())
            ->method('create')
            ->with($orderId)
            ->willReturn(false);

        $this->dispatch(self::$entryPoint);

        $this->assertRedirect($this->stringContains('backend/sales/order/view'));
        $this->assertSessionMessages(
            $this->equalTo(['Sorry, we can&#039;t submit order for Guarantee.']),
            MessageInterface::TYPE_ERROR
        );
    }

    /**
     * Returns orderId from case in fixture
     *
     * @return int
     */
    private function getOrderId()
    {
        $caseId = 123;
        /** @var CaseRepositoryInterface $caseRepository */
        $caseRepository = $this->_objectManager->get(CaseRepositoryInterface::class);
        /** @var CaseInterface $caseEntity */
        $caseEntity = $caseRepository->getByCaseId($caseId);

        return $caseEntity->getOrderId();
    }
}
