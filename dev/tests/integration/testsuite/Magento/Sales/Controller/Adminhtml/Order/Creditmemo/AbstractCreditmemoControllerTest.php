<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Controller\Adminhtml\Order\Creditmemo;

use Magento\Framework\Api\SearchCriteria;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Data\Form\FormKey;
use Magento\Sales\Api\Data\CreditmemoInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\OrderRepository;
use Magento\TestFramework\Mail\Template\TransportBuilderMock;
use Magento\TestFramework\TestCase\AbstractBackendController;

/**
 * Abstract backend creditmemo test.
 */
class AbstractCreditmemoControllerTest extends AbstractBackendController
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
    protected $resource = 'Magento_Sales::sales_creditmemo';

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
        /** @var OrderInterface|null $order */
        $order = reset($orders);

        return $order;
    }

    /**
     * @param OrderInterface $order
     * @return CreditmemoInterface
     */
    protected function getCreditMemo(OrderInterface $order): CreditmemoInterface
    {
        /** @var \Magento\Sales\Model\ResourceModel\Order\Creditmemo\Collection $creditMemoCollection */
        $creditMemoCollection = $this->_objectManager->create(
            \Magento\Sales\Model\ResourceModel\Order\Creditmemo\CollectionFactory::class
        )->create();

        /** @var CreditmemoInterface $creditMemo */
        $creditMemo = $creditMemoCollection
            ->setOrderFilter($order)
            ->setPageSize(1)
            ->getFirstItem();

        return $creditMemo;
    }
}
