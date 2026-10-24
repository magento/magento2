<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */

declare(strict_types=1);

namespace Magento\Sales\Model\Order\Invoice\Sender;

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\ObjectManagerInterface;
use Magento\Sales\Api\Data\InvoiceInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\ResourceModel\Order\Invoice\CollectionFactory;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;
use Magento\Sales\Model\Order\Invoice\Sender\EmailSender;
use Magento\TestFramework\Mail\Template\TransportBuilderMock;

/**
 * Test Order\Invoice\Sender\EmailSender model
 *
 * @see \Magento\Sales\Model\Order\Invoice\Sender\EmailSender
 * @magentoDbIsolation enabled
 * @magentoDataFixture Magento/Sales/_files/invoice.php
 */
class EmailSenderTest extends TestCase
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var EmailSender
     */
    private $emailSender;

    /**
     * @var CollectionFactory
     */
    private $invoiceCollectionFactory;

    /**
     * @var TransportBuilderMock
     */
    private $transportBuilder;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->objectManager = Bootstrap::getObjectManager();
        $this->emailSender = $this->objectManager->get(EmailSender::class);
        $this->invoiceCollectionFactory = $this->objectManager->get(CollectionFactory::class);
        $this->transportBuilder = $this->objectManager->get(TransportBuilderMock::class);
    }

    /**
     * Test that order item(s) present in email
     *
     * @magentoAppArea frontend
     * @return void
     * @throws \Exception
     */
    public function testOrderItemsPresentInEmail()
    {
        $order = $this->getOrder('100000001');
        $invoice = $this->getInvoiceByOrder($order);
        $this->emailSender->send($order, $invoice);
        $message = $this->transportBuilder->getSentMessage();
        $this->assertStringContainsString(
            'SKU: simple',
            quoted_printable_decode($message->getBody()->bodyToString()),
            'Expected text wasn\'t found in message.'
        );
    }

    /**
     * Get first order invoice
     *
     * @param OrderInterface|int $order
     * @return InvoiceInterface
     */
    protected function getInvoiceByOrder($order): InvoiceInterface
    {
        $invoiceCollection = $this->invoiceCollectionFactory->create();

        return $invoiceCollection->setOrderFilter($order)->setPageSize(1)->getFirstItem();
    }

    /**
     * Gets order entity by increment id.
     *
     * @param string $incrementId
     * @return OrderInterface
     */
    private function getOrder(string $incrementId): OrderInterface
    {
        /** @var SearchCriteriaBuilder $searchCriteriaBuilder */
        $searchCriteriaBuilder = $this->objectManager->get(SearchCriteriaBuilder::class);
        $searchCriteria = $searchCriteriaBuilder->addFilter('increment_id', $incrementId)
            ->create();

        /** @var OrderRepositoryInterface $repository */
        $repository = $this->objectManager->get(OrderRepositoryInterface::class);
        $items = $repository->getList($searchCriteria)
            ->getItems();

        return array_pop($items);
    }
}
