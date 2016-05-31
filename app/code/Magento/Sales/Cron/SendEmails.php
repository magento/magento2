<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Cron;

/**
 * Sales emails sending observer.
 *
 * Performs handling of cron jobs related to sending emails to customers
 * after creation/modification of Order, Invoice, Shipment or Creditmemo.
 */
class SendEmails
{
    /**
     * Global configuration storage.
     *
     * @var \Magento\Sales\Model\EmailSenderHandler
     */
    protected $emailSenderHandler;

    /**
     * @param \Magento\Sales\Model\EmailSenderHandler $emailSenderHandler
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
     * @return void
     */
    public function execute()
    {
        $this->emailSenderHandler->sendEmails();
    }
}
