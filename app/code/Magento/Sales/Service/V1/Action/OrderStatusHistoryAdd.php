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
namespace Magento\Sales\Service\V1\Action;

use Magento\Sales\Model\OrderRepository;
use Magento\Sales\Model\Order\Status\HistoryConverter;
use Magento\Sales\Service\V1\Data\OrderStatusHistory;

/**
 * Class OrderStatusHistoryAdd
 * @package Magento\Sales\Service\V1
 */
class OrderStatusHistoryAdd
{
    /**
     * @var OrderRepository
     */
    protected $orderRepository;

    /**
     * @var HistoryConverter
     */
    protected $historyConverter;

    /**
     * @param OrderRepository $orderRepository
     * @param HistoryConverter $historyConverter
     */
    public function __construct(
        OrderRepository $orderRepository,
        HistoryConverter $historyConverter
    ) {
        $this->orderRepository = $orderRepository;
        $this->historyConverter = $historyConverter;
    }

    /**
     * Invoke service
     *
     * @param int $id
     * @param \Magento\Sales\Service\V1\Data\OrderStatusHistory $statusHistory
     * @return bool
     */
    public function invoke($id, OrderStatusHistory $statusHistory)
    {
        $order = $this->orderRepository->get($id);
        $order->addStatusHistory($this->historyConverter->getModel($statusHistory));
        $order->save();
        return true;
    }
}
