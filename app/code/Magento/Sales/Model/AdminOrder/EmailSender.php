<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Sales\Model\AdminOrder;

use Magento\Framework\Logger;
use Magento\Framework\Message\ManagerInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Email\Sender\OrderSender;

/**
 * Class EmailSender
 */
class EmailSender
{
    /**
     * @var ManagerInterface
     */
    protected $messageManager;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @var OrderSender
     */
    protected $orderSender;

    /**
     * @param ManagerInterface $messageManager
     * @param Logger $logger
     * @param OrderSender $orderSender
     */
    public function __construct(ManagerInterface $messageManager, Logger $logger, OrderSender $orderSender)
    {
        $this->messageManager = $messageManager;
        $this->logger = $logger;
        $this->orderSender = $orderSender;
    }

    /**
     * Send email about new order.
     * Process mail exception
     *
     * @param Order $order
     * @return bool
     */
    public function send(Order $order)
    {
        try {
            $this->orderSender->send($order);
        } catch (\Magento\Framework\Mail\Exception $exception) {
            $this->logger->logException($exception);
            $this->messageManager->addWarning(
                __('You did not email your customer. Please check your email settings.')
            );
            return false;
        }

        return true;
    }
}
