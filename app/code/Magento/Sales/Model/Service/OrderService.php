<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Service;

use Magento\Sales\Api\OrderManagementInterface;

/**
 * Class OrderService
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @since 2.0.0
 */
class OrderService implements OrderManagementInterface
{
    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     * @since 2.0.0
     */
    protected $orderRepository;

    /**
     * @var \Magento\Sales\Api\OrderStatusHistoryRepositoryInterface
     * @since 2.0.0
     */
    protected $historyRepository;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     * @since 2.0.0
     */
    protected $criteriaBuilder;

    /**
     * @var \Magento\Framework\Api\FilterBuilder
     * @since 2.0.0
     */
    protected $filterBuilder;

    /**
     * @var \Magento\Sales\Model\OrderNotifier
     * @since 2.0.0
     */
    protected $notifier;

    /**
     * @var \Magento\Framework\Event\ManagerInterface
     * @since 2.0.0
     */
    protected $eventManager;

    /**
     * @var \Magento\Sales\Model\Order\Email\Sender\OrderCommentSender
     * @since 2.0.0
     */
    protected $orderCommentSender;

    /**
     * Constructor
     *
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
     * @param \Magento\Sales\Api\OrderStatusHistoryRepositoryInterface $historyRepository
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $criteriaBuilder
     * @param \Magento\Framework\Api\FilterBuilder $filterBuilder
     * @param \Magento\Sales\Model\OrderNotifier $notifier
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Sales\Model\Order\Email\Sender\OrderCommentSender $orderCommentSender
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Sales\Api\OrderStatusHistoryRepositoryInterface $historyRepository,
        \Magento\Framework\Api\SearchCriteriaBuilder $criteriaBuilder,
        \Magento\Framework\Api\FilterBuilder $filterBuilder,
        \Magento\Sales\Model\OrderNotifier $notifier,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Sales\Model\Order\Email\Sender\OrderCommentSender $orderCommentSender
    ) {
        $this->orderRepository = $orderRepository;
        $this->historyRepository = $historyRepository;
        $this->criteriaBuilder = $criteriaBuilder;
        $this->filterBuilder = $filterBuilder;
        $this->notifier = $notifier;
        $this->eventManager = $eventManager;
        $this->orderCommentSender = $orderCommentSender;
    }

    /**
     * Order cancel
     *
     * @param int $id
     * @return bool
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function getCommentsList($id)
    {
        $this->criteriaBuilder->addFilters(
            [$this->filterBuilder->setField('parent_id')->setValue($id)->setConditionType('eq')->create()]
        );
        $searchCriteria = $this->criteriaBuilder->create();
        return $this->historyRepository->getList($searchCriteria);
    }

    /**
     * Add comment to order
     *
     * @param int $id
     * @param \Magento\Sales\Api\Data\OrderStatusHistoryInterface $statusHistory
     * @return bool
     * @since 2.0.0
     */
    public function addComment($id, \Magento\Sales\Api\Data\OrderStatusHistoryInterface $statusHistory)
    {
        $order = $this->orderRepository->get($id);
        $order->addStatusHistory($statusHistory);
        $this->orderRepository->save($order);
        $notify = isset($statusHistory['is_customer_notified']) ? $statusHistory['is_customer_notified'] : false;
        $comment = trim(strip_tags($statusHistory->getComment()));
        $this->orderCommentSender->send($order, $notify, $comment);
        return true;
    }

    /**
     * Notify user
     *
     * @param int $id
     * @return bool
     * @since 2.0.0
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
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function hold($id)
    {
        $order = $this->orderRepository->get($id);
        $order->hold();
        return (bool)$this->orderRepository->save($order);
    }

    /**
     * Order un hold
     *
     * @param int $id
     * @return bool
     * @since 2.0.0
     */
    public function unHold($id)
    {
        $object = $this->orderRepository->get($id);
        $object->unhold();
        return (bool)$this->orderRepository->save($object);
    }

    /**
     * @param \Magento\Sales\Api\Data\OrderInterface $order
     * @return \Magento\Sales\Api\Data\OrderInterface
     * @throws \Exception
     * @since 2.0.0
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
     * @return \Magento\Sales\Model\Service\OrderService
     * @throws \Magento\Framework\Exception\LocalizedException
     * @since 2.0.0
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

        $transport = new \Magento\Framework\DataObject(
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
