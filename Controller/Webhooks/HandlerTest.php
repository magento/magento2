<?php
/**
 * Copyright © 2017 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Controller\Webhooks;

use Magento\TestFramework\TestCase\AbstractController;
use Magento\Signifyd\Model\SignifydGateway\Response\WebhookRequest;
use Magento\Signifyd\Api\CaseRepositoryInterface;
use Magento\Signifyd\Api\Data\CaseInterface;
use Magento\Sales\Api\Data\OrderStatusHistoryInterface;
use Magento\Sales\Api\OrderRepositoryInterface;

/**
 * Class tests handling webhook post from Signifyd service.
 */
class HandlerTest extends AbstractController
{
    /**
     * @var string
     */
    private static $entryPoint = 'signifyd/webhooks/handler';

    /**
     * Tests handling webhook message of cases/rescore type.
     * Checks updated case entity and comment in order history.
     *
     * @covers \Magento\Signifyd\Controller\Webhooks\Handler::execute
     * @magentoConfigFixture current_store fraud_protection/signifyd/active 1
     * @magentoConfigFixture current_store fraud_protection/signifyd/api_key ApFZZvxGgIxuP8BazSm3v8eGN
     * @magentoDataFixture Magento/Signifyd/_files/case.php
     */
    public function testExecuteSuccess()
    {
        $caseId = 123;
        $webhookRequest = $this->getWebhookRequest();
        $this->_objectManager->addSharedInstance($webhookRequest, WebhookRequest::class);

        $this->dispatch(self::$entryPoint);

        /** @var CaseRepositoryInterface $caseManagement */
        $caseRepository = $this->_objectManager->get(CaseRepositoryInterface::class);
        /** @var CaseInterface $caseEntity */
        $caseEntity = $caseRepository->getByCaseId($caseId);
        $orderEntityId = $caseEntity->getOrderId();

        static::assertNotEmpty($caseEntity);
        static::assertEquals('2017-01-06 12:47:03', $caseEntity->getCreatedAt());
        static::assertEquals('2017-01-06 12:47:03', $caseEntity->getUpdatedAt());
        static::assertEquals(CaseInterface::GUARANTEE_PENDING, $caseEntity->getGuaranteeDisposition());
        static::assertEquals('Magento', $caseEntity->getAssociatedTeam()['teamName']);
        static::assertEquals(true, $caseEntity->isGuaranteeEligible());
        static::assertEquals(CaseInterface::STATUS_OPEN, $caseEntity->getStatus());
        static::assertEquals($orderEntityId, $caseEntity->getOrderId());

        /** @var OrderRepositoryInterface $orderRepository */
        $orderRepository = $this->_objectManager->get(OrderRepositoryInterface::class);
        $order = $orderRepository->get($caseEntity->getOrderId());
        $histories = $order->getStatusHistories();
        static::assertNotEmpty($histories);

        /** @var OrderStatusHistoryInterface $caseCreationComment */
        $caseComment = array_pop($histories);
        static::assertInstanceOf(OrderStatusHistoryInterface::class, $caseComment);

        static::assertEquals(
            "Case Update: New score for the order is 384. Previous score was 553.",
            $caseComment->getComment()
        );

        $this->_objectManager->removeSharedInstance(WebhookRequest::class);
    }

    /**
     * Returns mocked WebhookRequest
     *
     * @return WebhookRequest|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getWebhookRequest()
    {
        $webhookRequest = $this->getMockBuilder(WebhookRequest::class)
            ->disableOriginalConstructor()
            ->getMock();
        $webhookRequest->expects($this->any())
            ->method('getBody')
            ->willReturn(file_get_contents(__DIR__ . '/../../_files/webhook_body.json'));
        $webhookRequest->expects($this->any())
            ->method('getEventTopic')
            ->willReturn('cases/rescore');
        $webhookRequest->expects($this->any())
            ->method('getHash')
            ->willReturn('m/X29RcHWPSCDPgQuSXjnyTfKISJDopcdGbVsRLeqy8=');

        return $webhookRequest;
    }
}
