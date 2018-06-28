<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Plugin;

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Registry;
use Magento\Payment\Model\Info as PaymentInfo;
use Magento\Paypal\Model\Api\Nvp;
use Magento\Paypal\Model\Config;
use Magento\Paypal\Model\Express;
use Magento\Paypal\Model\Info;
use Magento\Paypal\Model\Pro;
use Magento\Paypal\Model\ProFactory;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Signifyd\Api\CaseRepositoryInterface;
use Magento\Signifyd\Api\Data\CaseInterface;
use Magento\Signifyd\Model\SignifydGateway\ApiClient;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\ObjectManager;
use PHPUnit\Framework\MockObject_MockObject as MockObject;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class DenyPaymentTest extends \PHPUnit\Framework\TestCase
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
     * @var Registry
     */
    private $registry;

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

        $this->registry = $this->objectManager->get(Registry::class);

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
     * Checks a test case, when payment has been denied
     * and calls plugin to cancel Signifyd case guarantee.
     *
     * @covers \Magento\Signifyd\Plugin\PaymentPlugin::afterDenyPayment
     * @magentoDataFixture Magento/Signifyd/_files/approved_case.php
     * @magentoConfigFixture current_store fraud_protection/signifyd/active 1
     */
    public function testAfterDenyPayment()
    {
        $order = $this->getOrder();
        $this->registry->register('current_order', $order);

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

        /** @var \Magento\Sales\Model\Order\Payment $payment */
        $payment = $order->getPayment();
        $payment->setData('method_instance', $this->getMethodInstance());
        $payment->deny();

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

    /**
     * Gets payment method instance.
     *
     * @return Express
     */
    private function getMethodInstance()
    {
        /** @var PaymentInfo $infoInstance */
        $infoInstance = $this->objectManager->get(PaymentInfo::class);
        $infoInstance->setAdditionalInformation(
            Info::PAYMENT_STATUS_GLOBAL,
            Info::PAYMENTSTATUS_PENDING
        );
        $infoInstance->setAdditionalInformation(
            Info::PENDING_REASON_GLOBAL,
            Info::PAYMENTSTATUS_PENDING
        );

        /** @var Express $methodInstance */
        $methodInstance = $this->objectManager->create(
            Express::class,
            ['proFactory' => $this->getProFactory()]
        );
        $methodInstance->setData('info_instance', $infoInstance);

        return $methodInstance;
    }

    /**
     * Gets Pro factory mock.
     *
     * @return ProFactory|MockObject
     */
    protected function getProFactory()
    {
        $pro = $this->getMockBuilder(Pro::class)
            ->disableOriginalConstructor()
            ->setMethods(['getApi', 'setMethod', 'getConfig', '__wakeup', 'reviewPayment'])
            ->getMock();
        $nvpClient = $this->getMockBuilder(Nvp::class)
            ->disableOriginalConstructor()
            ->getMock();

        $pro->method('getConfig')
            ->willReturn($this->getConfig());
        $pro->method('getApi')
            ->willReturn($nvpClient);
        $pro->method('reviewPayment')
            ->willReturn(true);

        $proFactory = $this->getMockBuilder(ProFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $proFactory->method('create')
            ->willReturn($pro);

        return $proFactory;
    }

    /**
     * Gets config mock.
     *
     * @return Config|MockObject
     */
    protected function getConfig()
    {
        $config = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->getMock();
        $config->method('getValue')
            ->with('payment_action')
            ->willReturn(Config::PAYMENT_ACTION_AUTH);

        return $config;
    }
}
