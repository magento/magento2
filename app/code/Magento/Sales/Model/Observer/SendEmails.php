<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Observer;

/**
 * Sales emails sending observer.
 *
 * Performs handling of cron jobs related to sending emails to customers
 * after creation/modification of Order, Invoice, Shipment or Creditmemo.
 */
class SendEmails
{
    /**
     * Email sender model.
     *
     * @var \Magento\Sales\Model\Order\Email\Sender
     */
    protected $emailSender;

    /**
     * Entity resource model.
     *
     * @var \Magento\Sales\Model\Resource\EntityAbstract
     */
    protected $entityResource;

    /**
     * Entity collection model.
     *
     * @var \Magento\Sales\Model\Resource\Collection\AbstractCollection
     */
    protected $entityCollection;

    /**
     * Global configuration storage.
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $globalConfig;

    /**
     * @param \Magento\Sales\Model\Order\Email\Sender $emailSender
     * @param \Magento\Sales\Model\Resource\EntityAbstract $entityResource
     * @param \Magento\Sales\Model\Resource\Collection\AbstractCollection $entityCollection
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $globalConfig
     */
    public function __construct(
        \Magento\Sales\Model\Order\Email\Sender $emailSender,
        \Magento\Sales\Model\Resource\EntityAbstract $entityResource,
        \Magento\Sales\Model\Resource\Collection\AbstractCollection $entityCollection,
        \Magento\Framework\App\Config\ScopeConfigInterface $globalConfig
    ) {
        $this->emailSender = $emailSender;
        $this->entityResource = $entityResource;
        $this->entityCollection = $entityCollection;
        $this->globalConfig = $globalConfig;
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
        if ($this->globalConfig->getValue('sales_email/general/async_sending')) {
            $this->entityCollection->addFieldToFilter('send_email', ['eq' => 1]);
            $this->entityCollection->addFieldToFilter('email_sent', ['null' => true]);

            /** @var \Magento\Sales\Model\AbstractModel $item */
            foreach ($this->entityCollection->getItems() as $item) {
                if ($this->emailSender->send($item, true)) {
                    $this->entityResource->save(
                        $item->setEmailSent(true)
                    );
                }
            }
        }
    }
}
