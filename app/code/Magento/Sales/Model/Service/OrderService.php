<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Sales\Model\Service;

use Magento\Sales\Api\OrderManagementInterface;

/**
 * Class OrderService
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
     * Constructor
     *
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
     * @param \Magento\Sales\Api\OrderStatusHistoryRepositoryInterface $historyRepository
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $criteriaBuilder
     * @param \Magento\Framework\Api\FilterBuilder $filterBuilder
     * @param \Magento\Sales\Model\OrderNotifier $notifier
     */
    public function __construct(
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Sales\Api\OrderStatusHistoryRepositoryInterface $historyRepository,
        \Magento\Framework\Api\SearchCriteriaBuilder $criteriaBuilder,
        \Magento\Framework\Api\FilterBuilder $filterBuilder,
        \Magento\Sales\Model\OrderNotifier $notifier
    ) {
        $this->orderRepository = $orderRepository;
        $this->historyRepository = $historyRepository;
        $this->criteriaBuilder = $criteriaBuilder;
        $this->filterBuilder = $filterBuilder;
        $this->notifier = $notifier;
    }

    /**
     * Order cancel
     *
     * @param int $id
     * @return bool
     */
    public function cancel($id)
    {
        return (bool)$this->orderRepository->get($id)->cancel();
    }

    /**
     * Returns list of comments attached to order
     *
     * @param int $id
     * @return \Magento\Sales\Api\Data\OrderStatusHistorySearchResultInterface
     */
    public function getCommentsList($id)
    {
        $this->criteriaBuilder->addFilter(
            ['eq' => $this->filterBuilder->setField('parent_id')->setValue($id)->create()]
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
}
