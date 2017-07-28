<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Observer\Virtual;

use Magento\Framework\Event\ObserverInterface;

/**
 * Sales emails sending observer.
 *
 * Performs handling of cron jobs related to sending emails to customers
 * after creation/modification of Order, Invoice, Shipment or Creditmemo.
 * @since 2.0.0
 */
class SendEmails implements ObserverInterface
{
    /**
     * Global configuration storage.
     *
     * @var \Magento\Sales\Model\EmailSenderHandler
     * @since 2.0.0
     */
    protected $emailSenderHandler;

    /**
     * @param \Magento\Sales\Model\EmailSenderHandler $emailSenderHandler
     * @since 2.0.0
     */
    public function __construct(\Magento\Sales\Model\EmailSenderHandler $emailSenderHandler)
    {
        $this->emailSenderHandler = $emailSenderHandler;
    }

    /**
     * Handles asynchronous email sending during corresponding
     * cron job.
     *
     * Also method is used in the next events:
     *
     * - config_data_sales_email_general_async_sending_disabled
     *
     * Works only if asynchronous email sending is enabled
     * in global settings.
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @since 2.0.0
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $this->emailSenderHandler->sendEmails();
    }
}
