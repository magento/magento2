<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Checkout\Api;

use Braintree\Result\Error;
use Magento\Braintree\Gateway\Http\Client\TransactionSale;
use Magento\Braintree\Model\Ui\ConfigProvider;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\State;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Api\Data\PaymentInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\ObjectManager;
use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

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
     */
    public function testSavePaymentInformationAndPlaceOrderWithErrors(string $area, string $errorMessage)
    {
        /** @var State $state */
        $state = $this->objectManager->get(State::class);
        $state->setAreaCode($area);

        $quote = $this->getQuote('test_order_1');

        /** @var PaymentInterface $payment */
        $payment = $this->objectManager->create(PaymentInterface::class);
        $payment->setMethod(ConfigProvider::CODE);

        $errors = [
            'errors' => [
                [
                    'code' => 'fake_code',
                    'attribute' => 'base',
                    'message' => 'Error message should not be mapped.'
                ],
                [
                    'code' => 81802,
                    'attribute' => 'base',
                    'message' => 'Company is too long.'
                ],
                [
                    'code' => 91511,
                    'attribute' => 'base',
                    'message' => 'Customer does not have any credit cards.'
                ]
            ]
        ];
        $response = new Error(['errors' => $errors]);

        $this->client->method('placeRequest')
            ->willReturn(['object' => $response]);

        $this->expectExceptionMessage($errorMessage);

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
        $globalAreaError = 'Company is too long.';
        return [
            ['area' => 'frontend', 'error' => $globalAreaError],
            ['area' => 'adminhtml', 'error' => $globalAreaError . PHP_EOL . 'Customer does not have any credit cards.'],
        ];
    }

    /**
     * Retrieves quote by provided order ID.
     *
     * @param string $reservedOrderId
     * @return CartInterface
     */
    private function getQuote(string $reservedOrderId) : CartInterface
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
