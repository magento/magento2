<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Paypal\Controller\Transparent;

use Magento\Checkout\Model\Session;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Intl\DateTimeFactory;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Api\PaymentMethodManagementInterface;

/**
 * Tests PayPal transparent response controller.
 */
class ResponseTest extends \Magento\TestFramework\TestCase\AbstractController
{
    /**
     * Tests setting credit card expiration month and year to payment from PayPal response.
     *
     * @param string $currentDateTime
     * @param string $paypalExpDate
     * @param int $expectedCcMonth
     * @param int $expectedCcYear
     * @throws NoSuchEntityException
     *
     * @magentoConfigFixture current_store payment/payflowpro/active 1
     * @magentoDataFixture Magento/Sales/_files/quote.php
     * @dataProvider paymentCcExpirationDateDataProvider
     */
    public function testPaymentCcExpirationDate(
        string $currentDateTime,
        string $paypalExpDate,
        int $expectedCcMonth,
        int $expectedCcYear
    ) {
        $reservedOrderId = 'test01';
        $postData = [
            'EXPDATE' => $paypalExpDate,
            'AMT' => '0.00',
            'RESPMSG' => 'Verified',
            'CVV2MATCH' => 'Y',
            'PNREF' => 'A10AAD866C87',
            'SECURETOKEN' => '3HYEHfG06skydAdBXbpIl8QJZ',
            'AVSDATA' => 'YNY',
            'RESULT' => '0',
            'IAVS' => 'N',
            'AVSADDR' => 'Y',
            'SECURETOKENID' => 'yqanLisRZbI0HAG8q3SbbKbhiwjNZAGf',
        ];

        $quote = $this->getQuote($reservedOrderId);
        $this->getRequest()->setPostValue($postData);
        $this->getRequest()->setMethod('POST');
        /** @var Session $checkoutSession */
        $checkoutSession = $this->_objectManager->get(Session::class);
        $checkoutSession->setQuoteId($quote->getId());
        $this->setCurrentDateTime($currentDateTime);

        $this->dispatch('paypal/transparent/response');

        /** @var PaymentMethodManagementInterface $paymentManagment */
        $paymentManagment = $this->_objectManager->get(PaymentMethodManagementInterface::class);
        $payment = $paymentManagment->get($quote->getId());

        $this->assertEquals($expectedCcMonth, $payment->getCcExpMonth());
        $this->assertEquals($expectedCcYear, $payment->getCcExpYear());
    }

    /**
     * @return array
     */
    public function paymentCcExpirationDateDataProvider(): array
    {
        return [
            'Expiration year in current century' => [
                'currentDateTime' => '2019-07-05 00:00:00',
                'paypalExpDate' => '0321',
                'expectedCcMonth' => 3,
                'expectedCcYear' => 2021
            ],
            'Expiration year in next century' => [
                'currentDateTime' => '2099-01-01 00:00:00',
                'paypalExpDate' => '1002',
                'expectedCcMonth' => 10,
                'expectedCcYear' => 2102
            ]
        ];
    }

    /**
     * Sets current date and time.
     *
     * @param string $date
     */
    private function setCurrentDateTime(string $dateTime): void
    {
        $dateTime = new \DateTime($dateTime, new \DateTimeZone('UTC'));
        $dateTimeFactory = $this->getMockBuilder(DateTimeFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $dateTimeFactory->method('create')
            ->willReturn($dateTime);

        $this->_objectManager->addSharedInstance($dateTimeFactory, DateTimeFactory::class);
    }

    /**
     * Gets quote by reserved order ID.
     *
     * @param string $reservedOrderId
     * @return CartInterface
     */
    private function getQuote(string $reservedOrderId): CartInterface
    {
        $searchCriteria = $this->_objectManager->get(SearchCriteriaBuilder::class)
            ->addFilter('reserved_order_id', $reservedOrderId)
            ->create();

        /** @var CartRepositoryInterface $quoteRepository */
        $quoteRepository = $this->_objectManager->get(CartRepositoryInterface::class);
        $items = $quoteRepository->getList($searchCriteria)
            ->getItems();

        return array_pop($items);
    }
}
