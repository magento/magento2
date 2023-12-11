<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Controller\Adminhtml\Order\Invoice;

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\Request\Http;
use Magento\Sales\Api\Data\InvoiceInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\OrderRepository;
use Magento\Sales\Model\ResourceModel\Order\Invoice\CollectionFactory;
use Magento\TestFramework\Mail\Template\TransportBuilderMock;
use Magento\TestFramework\TestCase\AbstractBackendController;

/**
 * Abstract backend invoice test.
 */
abstract class AbstractInvoiceControllerTest extends AbstractBackendController
{
    /** @var TransportBuilderMock */
    protected $transportBuilder;

    /** @var string */
    protected $resource = 'Magento_Sales::sales_invoice';

    /** @var OrderRepository */
    private $orderRepository;

    /** @var SearchCriteriaBuilder */
    private $searchCriteriaBuilder;

    /** @var CollectionFactory */
    private $invoiceCollectionFactory;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->transportBuilder = $this->_objectManager->get(TransportBuilderMock::class);
        $this->orderRepository = $this->_objectManager->get(OrderRepository::class);
        $this->searchCriteriaBuilder = $this->_objectManager->get(SearchCriteriaBuilder::class);
        $this->invoiceCollectionFactory = $this->_objectManager->get(CollectionFactory::class);
    }

    /**
     * Retrieve order
     *
     * @param string $incrementalId
     * @return OrderInterface|null
     */
    protected function getOrder(string $incrementalId): ?OrderInterface
    {
        $searchCriteria = $this->searchCriteriaBuilder->addFilter(OrderInterface::INCREMENT_ID, $incrementalId)
            ->create();
        $orders = $this->orderRepository->getList($searchCriteria)->getItems();

        return reset($orders);
    }

    /**
     * Get firs order invoice
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
     * Prepare request
     *
     * @param array $postParams
     * @param array $params
     * @return void
     */
    protected function prepareRequest(array $postParams = [], array $params = []): void
    {
        $this->getRequest()->setMethod(Http::METHOD_POST);
        $this->getRequest()->setParams($params);
        $this->getRequest()->setPostValue($postParams);
    }

    /**
     * Normalize post parameters
     *
     * @param array $items
     * @param string $commentText
     * @param bool $doShipment
     * @param bool $sendEmail
     * @return array
     */
    protected function hydratePost(
        array $items,
        string $commentText = '',
        $doShipment = false,
        $sendEmail = false
    ): array {
        return [
            'invoice' => [
                'items' => $items,
                'comment_text' => $commentText,
                'do_shipment' => $doShipment,
                'send_email' => $sendEmail
            ],
        ];
    }
}
