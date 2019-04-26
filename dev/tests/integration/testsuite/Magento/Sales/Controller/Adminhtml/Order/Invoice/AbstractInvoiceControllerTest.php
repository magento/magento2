<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Controller\Adminhtml\Order\Invoice;

use Magento\Framework\Api\SearchCriteria;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Data\Form\FormKey;
use Magento\Sales\Api\Data\InvoiceInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\OrderRepository;
use Magento\TestFramework\Mail\Template\TransportBuilderMock;
use Magento\TestFramework\TestCase\AbstractBackendController;

/**
 * Abstract backend invoice test.
 */
class AbstractInvoiceControllerTest extends AbstractBackendController
{
    /**
     * @var TransportBuilderMock
     */
    protected $transportBuilder;

    /**
     * @var OrderRepository
     */
    protected $orderRepository;

    /**
     * @var FormKey
     */
    protected $formKey;

    /**
     * @var string
     */
    protected $resource = 'Magento_Sales::sales_invoice';

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        parent::setUp();
        $this->transportBuilder = $this->_objectManager->get(TransportBuilderMock::class);
        $this->orderRepository = $this->_objectManager->get(OrderRepository::class);
        $this->formKey = $this->_objectManager->get(FormKey::class);
    }

    /**
     * @param string $incrementalId
     * @return OrderInterface|null
     */
    protected function getOrder(string $incrementalId)
    {
        /** @var SearchCriteria $searchCriteria */
        $searchCriteria = $this->_objectManager->create(SearchCriteriaBuilder::class)
            ->addFilter(OrderInterface::INCREMENT_ID, $incrementalId)
            ->create();

        $orders = $this->orderRepository->getList($searchCriteria)->getItems();
        /** @var OrderInterface $order */
        $order = reset($orders);

        return $order;
    }

    /**
     * @param OrderInterface $order
     * @return InvoiceInterface
     */
    protected function getInvoiceByOrder(OrderInterface $order): InvoiceInterface
    {
        /** @var \Magento\Sales\Model\ResourceModel\Order\Invoice\Collection $invoiceCollection */
        $invoiceCollection = $this->_objectManager->create(
            \Magento\Sales\Model\ResourceModel\Order\Invoice\CollectionFactory::class
        )->create();

        /** @var InvoiceInterface $invoice */
        $invoice = $invoiceCollection
            ->setOrderFilter($order)
            ->setPageSize(1)
            ->getFirstItem();

        return $invoice;
    }
}
