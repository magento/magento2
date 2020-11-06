<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Controller\Adminhtml\Order\Create;

use Magento\Customer\Model\Session;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Escaper;
use Magento\Framework\Registry;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Sales\Api\Data\OrderInterfaceFactory;
use Magento\TestFramework\Core\Version\View;
use Magento\TestFramework\Request;
use Magento\TestFramework\TestCase\AbstractBackendController;

/**
 * Test for reorder controller.
 *
 * @see \Magento\Sales\Controller\Adminhtml\Order\Create\Reorder
 * @magentoAppArea adminhtml
 */
class ReorderTest extends AbstractBackendController
{
    /** @var OrderInterfaceFactory */
    private $orderFactory;

    /** @var CartRepositoryInterface */
    private $quoteRepository;

    /** @var CartInterface */
    private $quote;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->orderFactory = $this->_objectManager->get(OrderInterfaceFactory::class);
        $this->quoteRepository = $this->_objectManager->get(CartRepositoryInterface::class);
    }

    /**
     * @inheritdoc
     */
    protected function tearDown(): void
    {
        if ($this->quote instanceof CartInterface) {
            $this->quoteRepository->delete($this->quote);
        }

        parent::tearDown();
    }

    /**
     * Reorder with JS calendar options
     *
     * @magentoConfigFixture current_store catalog/custom_options/use_calendar 1
     * @magentoDataFixture Magento/Sales/_files/order_with_date_time_option_product.php
     *
     * @return void
     */
    public function testReorderAfterJSCalendarEnabled(): void
    {
        $order = $this->orderFactory->create()->loadByIncrementId('100000001');
        $this->dispatchReorderRequest((int)$order->getId());
        $this->assertRedirect($this->stringContains('backend/sales/order_create'));
        $this->quote = $this->getQuote('customer@example.com');
        $this->assertTrue(!empty($this->quote));
    }

    /**
     * Dispatch reorder request.
     *
     * @param null|int $orderId
     * @return void
     */
    private function dispatchReorderRequest(?int $orderId = null): void
    {
        $this->getRequest()->setMethod(Request::METHOD_GET);
        $this->getRequest()->setParam('order_id', $orderId);
        $this->dispatch('backend/sales/order_create/reorder');
    }

    /**
     * Gets quote by reserved order id.
     *
     * @return \Magento\Quote\Api\Data\CartInterface
     */
    private function getQuote(string $customerEmail): \Magento\Quote\Api\Data\CartInterface
    {
        /** @var SearchCriteriaBuilder $searchCriteriaBuilder */
        $searchCriteriaBuilder = $this->_objectManager->get(SearchCriteriaBuilder::class);
        $searchCriteria = $searchCriteriaBuilder->addFilter('customer_email', $customerEmail)
            ->create();

        /** @var CartRepositoryInterface $quoteRepository */
        $quoteRepository = $this->_objectManager->get(CartRepositoryInterface::class);
        $items = $quoteRepository->getList($searchCriteria)->getItems();

        return array_pop($items);
    }
}
