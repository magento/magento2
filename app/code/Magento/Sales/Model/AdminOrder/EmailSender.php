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
namespace Magento\Sales\Model\AdminOrder;

use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Logger;
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
