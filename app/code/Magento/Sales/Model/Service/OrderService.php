<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Service;

use Magento\Sales\Api\OrderManagementInterface;

/**
 * Class OrderService
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class OrderService implements OrderManagementInterface
{
    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * @var \Magento\Sales\Api\OrderStatusHistoryRepositoryInterface
     */
    protected $historyRepository;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    protected $criteriaBuilder;

    /**
     * @var \Magento\Framework\Api\FilterBuilder
     */
    protected $filterBuilder;

    /**
     * @var \Magento\Sales\Model\OrderNotifier
     */
    protected $notifier;

    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $eventManager;

    /**
     * Constructor
     *
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
     * @param \Magento\Sales\Api\OrderStatusHistoryRepositoryInterface $historyRepository
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $criteriaBuilder
     * @param \Magento\Framework\Api\FilterBuilder $filterBuilder
     * @param \Magento\Sales\Model\OrderNotifier $notifier
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     */
    public function __construct(
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Sales\Api\OrderStatusHistoryRepositoryInterface $historyRepository,
        \Magento\Framework\Api\SearchCriteriaBuilder $criteriaBuilder,
        \Magento\Framework\Api\FilterBuilder $filterBuilder,
        \Magento\Sales\Model\OrderNotifier $notifier,
        \Magento\Framework\Event\ManagerInterface $eventManager
    ) {
        $this->orderRepository = $orderRepository;
        $this->historyRepository = $historyRepository;
        $this->criteriaBuilder = $criteriaBuilder;
        $this->filterBuilder = $filterBuilder;
        $this->notifier = $notifier;
        $this->eventManager = $eventManager;
    }

    /**
     * Order cancel
     *
     * @param int $id
     * @return bool
     */
    public function cancel($id)
    {
        $order = $this->orderRepository->get($id);
        if ((bool)$order->cancel()) {
            $this->orderRepository->save($order);
            return true;
        }

        return false;
    }

    /**
     * Returns list of comments attached to order
     *
     * @param int $id
     * @return \Magento\Sales\Api\Data\OrderStatusHistorySearchResultInterface
     */
    public function getCommentsList($id)
    {
        $this->criteriaBuilder->addFilters(
            [$this->filterBuilder->setField('parent_id')->setValue($id)->setConditionType('eq')->create()]
        );
        $criteria = $this->criteriaBuilder->create();
        return $this->historyRepository->getList($criteria);
    }

    /**
     * Add comment to order
     *
     * @param int $id
     * @param \Magento\Sales\Api\Data\OrderStatusHistoryInterface $statusHistory
     * @return bool
     */
    public function addComment($id, \Magento\Sales\Api\Data\OrderStatusHistoryInterface $statusHistory)
    {
        $order = $this->orderRepository->get($id);
        $order->addStatusHistory($statusHistory);
        $this->orderRepository->save($order);
        return true;
    }

    /**
     * Notify user
     *
     * @param int $id
     * @return bool
     */
    public function notify($id)
    {
        $order = $this->orderRepository->get($id);
        return $this->notifier->notify($order);
    }

    /**
     * Returns order status
     *
     * @param int $id
     * @return string
     */
    public function getStatus($id)
    {
        return $this->orderRepository->get($id)->getStatus();
    }

    /**
     * Order hold
     *
     * @param int $id
     * @return bool
     */
    public function hold($id)
    {
        return (bool)$this->orderRepository->get($id)->hold();
    }

    /**
     * Order un hold
     *
     * @param int $id
     * @return bool
     */
    public function unHold($id)
    {
        return (bool)$this->orderRepository->get($id)->unhold();
    }

    /**
     * @param \Magento\Sales\Api\Data\OrderInterface $order
     * @return \Magento\Sales\Api\Data\OrderInterface
     * @throws \Exception
     */
    public function place(\Magento\Sales\Api\Data\OrderInterface $order)
    {
        // transaction will be here
        //begin transaction
        try {
            $order->place();
            return $this->orderRepository->save($order);
            //commit
        } catch (\Exception $e) {
            throw $e;
            //rollback;
        }
    }

    /**
     * Order state setter.
     *
     * If status is specified, will add order status history with specified comment
     * the setData() cannot be overridden because of compatibility issues with resource model
     * By default allows to set any state. Can also update status to default or specified value
     * Complete and closed states are encapsulated intentionally
     *
     * @param \Magento\Sales\Api\Data\OrderInterface $order
     * @param string $state
     * @param string|bool $status
     * @param string $comment
     * @param bool $isCustomerNotified
     * @param bool $shouldProtectState
     * @return \Magento\Sales\Model\Order
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function setState(
        \Magento\Sales\Api\Data\OrderInterface $order,
        $state,
        $status = false,
        $comment = '',
        $isCustomerNotified = null,
        $shouldProtectState = true
    ) {
        // attempt to set the specified state
        if ($shouldProtectState) {
            if ($order->isStateProtected($state)) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('The Order State "%1" must not be set manually.', $state)
                );
            }
        }

        $transport = new \Magento\Framework\Object(
            [
                'state'     => $state,
                'status'    => $status,
                'comment'   => $comment,
                'is_customer_notified'    => $isCustomerNotified
            ]
        );

        $this->eventManager->dispatch(
            'sales_order_state_change_before',
            ['order' => $this, 'transport' => $transport]
        );
        $status = $transport->getStatus();
        $order->setData('state', $transport->getState());

        // add status history
        if ($status) {
            if ($status === true) {
                $status = $order->getConfig()->getStateDefaultStatus($transport->getState());
            }
            $order->setStatus($status);
            $history = $order->addStatusHistoryComment($transport->getComment(), false);
            // no sense to set $status again
            $history->setIsCustomerNotified($transport->getIsCustomerNotified());
        }
        return $this;
    }
}
