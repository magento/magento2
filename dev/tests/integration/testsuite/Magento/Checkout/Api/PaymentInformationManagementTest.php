<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Checkout\Api;

use Braintree\Result\Error;
use Magento\Braintree\Gateway\Http\Client\TransactionSale;
use Magento\Braintree\Model\Ui\ConfigProvider;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\State;
use Magento\Framework\App\Area;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Api\Data\PaymentInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\ObjectManager;
use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * Class PaymentInformationManagementTest
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class PaymentInformationManagementTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var TransactionSale|MockObject
     */
    private $client;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->objectManager = Bootstrap::getObjectManager();

        $this->client = $this->getMockBuilder(TransactionSale::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->objectManager->addSharedInstance($this->client, TransactionSale::class);
    }

    /**
     * @inheritdoc
     */
    protected function tearDown()
    {
        $this->objectManager->removeSharedInstance(TransactionSale::class);
        parent::tearDown();
    }

    /**
     * Checks a case when payment method triggers an error during place order flow and
     * error messages from payment gateway should be mapped.
     * Error messages might be specific for different areas.
     *
     * @magentoAppIsolation enabled
     * @magentoDataFixture Magento/Checkout/_files/quote_with_shipping_method.php
     * @magentoConfigFixture current_store payment/braintree/active 1
     * @dataProvider getErrorPerAreaDataProvider
     * @expectedException \Magento\Framework\Exception\CouldNotSaveException
     * @param string $area
     * @param array $testErrorCodes
     * @param string $expectedOutput
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function testSavePaymentInformationAndPlaceOrderWithErrors(
        string $area,
        array $testErrorCodes,
        string $expectedOutput
    ) {
        /** @var State $state */
        $state = $this->objectManager->get(State::class);
        $state->setAreaCode($area);

        $quote = $this->getQuote('test_order_1');

        /** @var PaymentInterface $payment */
        $payment = $this->objectManager->create(PaymentInterface::class);
        $payment->setMethod(ConfigProvider::CODE);

        $errors = ['errors' => []];

        foreach ($testErrorCodes as $testErrorCode) {
            array_push($errors['errors'], ['code' => $testErrorCode]);
        }

        $response = new Error(['errors' => $errors]);

        $this->client->method('placeRequest')
            ->willReturn(['object' => $response]);

        $this->expectExceptionMessage($expectedOutput);

        /** @var PaymentInformationManagementInterface $paymentInformationManagement */
        $paymentInformationManagement = $this->objectManager->get(PaymentInformationManagementInterface::class);
        $paymentInformationManagement->savePaymentInformationAndPlaceOrder(
            $quote->getId(),
            $payment
        );
    }

    /**
     * Gets list of areas with specific error messages.
     *
     * @return array
     */
    public function getErrorPerAreaDataProvider()
    {
        $testErrorGlobal = ['code' => 81802, 'message' => 'Company is too long.'];
        $testErrorAdmin = ['code' => 91511, 'message' => 'Customer does not have any credit cards.'];
        $testErrorFake = ['code' => 'fake_code', 'message' => 'Error message should not be mapped.'];

        return [
            [
                Area::AREA_FRONTEND,
                [$testErrorAdmin['code'], $testErrorFake['code']],
                'Transaction has been declined. Please try again later.'
            ], [
                Area::AREA_FRONTEND,
                [$testErrorGlobal['code'], $testErrorAdmin['code'], $testErrorFake['code']],
                $testErrorGlobal['message']
            ], [
                Area::AREA_ADMINHTML,
                [$testErrorGlobal['code'], $testErrorAdmin['code'], $testErrorFake['code']],
                $testErrorGlobal['message'] . PHP_EOL . $testErrorAdmin['message']
            ],
        ];
    }

    /**
     * Retrieves quote by provided order ID.
     *
     * @param string $reservedOrderId
     * @return CartInterface
     */
    private function getQuote(string $reservedOrderId): CartInterface
    {
        /** @var SearchCriteriaBuilder $searchCriteriaBuilder */
        $searchCriteriaBuilder = $this->objectManager->get(SearchCriteriaBuilder::class);
        $searchCriteria = $searchCriteriaBuilder->addFilter('reserved_order_id', $reservedOrderId)
            ->create();

        /** @var CartRepositoryInterface $quoteRepository */
        $quoteRepository = $this->objectManager->get(CartRepositoryInterface::class);
        $items = $quoteRepository->getList($searchCriteria)
            ->getItems();

        return array_pop($items);
    }
}
