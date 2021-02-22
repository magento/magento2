<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Braintree\Model\Paypal\Helper;

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\ObjectManagerInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Model\Quote;
use Magento\TestFramework\Helper\Bootstrap;

class ShippingMethodUpdaterTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ShippingMethodUpdater
     */
    private $shippingMethodUpdater;

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->shippingMethodUpdater = $this->objectManager->get(ShippingMethodUpdater::class);
    }

    /**
     * Tests that shipping method is actually updated in quote.
     *
     * @magentoAppArea frontend
     * @magentoConfigFixture default_store carriers/flatrate/active 1
     * @magentoConfigFixture default_store carriers/freeshipping/active 1
     * @magentoDataFixture Magento/Braintree/Fixtures/paypal_quote.php
     */
    public function testExecute()
    {
        $reservedOrderId = 'test01';
        /** @var Quote $quote */
        $quote = $this->getQuote($reservedOrderId);

        $this->assertEquals(
            'flatrate_flatrate',
            $quote->getShippingAddress()->getShippingMethod()
        );

        $this->shippingMethodUpdater->execute('freeshipping_freeshipping', $quote);

        $this->assertEquals(
            'freeshipping_freeshipping',
            $quote->getShippingAddress()->getShippingMethod()
        );
    }

    /**
     * Gets quote by reserved order ID.
     *
     * @param string $reservedOrderId
     * @return CartInterface
     */
    private function getQuote(string $reservedOrderId): CartInterface
    {
        $searchCriteria = $this->objectManager->get(SearchCriteriaBuilder::class)
            ->addFilter('reserved_order_id', $reservedOrderId)
            ->create();

        /** @var CartRepositoryInterface $quoteRepository */
        $quoteRepository = $this->objectManager->get(CartRepositoryInterface::class);
        $items = $quoteRepository->getList($searchCriteria)
            ->getItems();

        return array_pop($items);
    }
}
