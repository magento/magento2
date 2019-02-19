<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Plugin;

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderManagementInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Signifyd\Api\CaseRepositoryInterface;
use Magento\Signifyd\Api\Data\CaseInterface;
use Magento\Signifyd\Model\SignifydGateway\ApiClient;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\ObjectManager;
use PHPUnit\Framework\MockObject_MockObject as MockObject;

class CancelOrderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var int
     */
    private static $caseId = 123;

    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var ApiClient|MockObject
     */
    private $apiClient;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->objectManager = Bootstrap::getObjectManager();

        $this->apiClient = $this->getMockBuilder(ApiClient::class)
            ->disableOriginalConstructor()
            ->setMethods(['makeApiCall'])
            ->getMock();

        $this->objectManager->addSharedInstance($this->apiClient, ApiClient::class);
    }

    /**
     * @inheritdoc
     */
    protected function tearDown()
    {
        $this->objectManager->removeSharedInstance(ApiClient::class);
    }

    /**
     * Checks a test case, when order has been cancelled
     * and calls plugin to cancel Signifyd case guarantee.
     *
     * @covers \Magento\Signifyd\Plugin\OrderPlugin::afterCancel
     * @magentoDataFixture Magento/Signifyd/_files/approved_case.php
     * @magentoConfigFixture current_store fraud_protection/signifyd/active 1
     */
    public function testAfterCancel()
    {
        $order = $this->getOrder();

        $this->apiClient->expects(self::once())
            ->method('makeApiCall')
            ->with(
                self::equalTo('/cases/' . self::$caseId . '/guarantee'),
                'PUT',
                [
                    'guaranteeDisposition' => CaseInterface::GUARANTEE_CANCELED
                ]
            )
            ->willReturn([
                'disposition' => CaseInterface::GUARANTEE_CANCELED
            ]);

        /** @var OrderManagementInterface $orderService */
        $orderService = $this->objectManager->get(OrderManagementInterface::class);
        $orderService->cancel($order->getEntityId());

        /** @var CaseRepositoryInterface $caseRepository */
        $caseRepository = $this->objectManager->get(CaseRepositoryInterface::class);
        $case = $caseRepository->getByCaseId(self::$caseId);

        self::assertEquals(CaseInterface::GUARANTEE_CANCELED, $case->getGuaranteeDisposition());
    }

    /**
     * Get stored order.
     *
     * @return OrderInterface
     */
    private function getOrder()
    {
        /** @var SearchCriteriaBuilder $searchCriteriaBuilder */
        $searchCriteriaBuilder = $this->objectManager->get(SearchCriteriaBuilder::class);
        $searchCriteria = $searchCriteriaBuilder->addFilter(OrderInterface::INCREMENT_ID, '100000001')
            ->create();

        $orderRepository = $this->objectManager->get(OrderRepositoryInterface::class);
        $orders = $orderRepository->getList($searchCriteria)
            ->getItems();

        /** @var OrderInterface $order */
        return array_pop($orders);
    }
}
