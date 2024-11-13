<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Paypal\Controller\Adminhtml\Transparent;

use Magento\Checkout\Model\Session;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Session\Generic;
use Magento\Paypal\Model\Payflow\Transparent;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Api\PaymentMethodManagementInterface;
use Magento\TestFramework\TestCase\AbstractController;

/**
 * Tests PayPal transparent response controller.
 *
 * @magentoAppArea adminhtml
 */
class ResponseTest extends AbstractController
{
    /**
     * Tests storing payment information on backend from PayPal response.
     *
     * @throws NoSuchEntityException
     *
     * @magentoConfigFixture current_store payment/payflowpro/active 1
     * @magentoDataFixture Magento/Sales/_files/quote.php
     */
    public function testStoringPaymentInformation()
    {
        $reservedOrderId = 'test01';
        $pnref = 'A10AAD866C87';
        $postData = [
            'EXPDATE' => '0321',
            'AMT' => '0.00',
            'RESPMSG' => 'Verified',
            'CVV2MATCH' => 'Y',
            'PNREF' => $pnref,
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
        $checkoutSession = $this->_objectManager->get(Generic::class);
        $checkoutSession->setQuoteId($quote->getId());

        $this->dispatch('backend/paypal/transparent/response');

        /** @var PaymentMethodManagementInterface $paymentManagment */
        $paymentManagment = $this->_objectManager->get(PaymentMethodManagementInterface::class);
        $payment = $paymentManagment->get($quote->getId());

        $this->assertEquals($pnref, $payment->getAdditionalInformation(Transparent::PNREF));
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
