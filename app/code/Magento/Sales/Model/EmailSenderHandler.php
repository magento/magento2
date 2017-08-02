<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model;

/**
 * Sales emails sending
 *
 * Performs handling of cron jobs related to sending emails to customers
 * after creation/modification of Order, Invoice, Shipment or Creditmemo.
 * @since 2.0.0
 */
class EmailSenderHandler
{
    /**
     * Email sender model.
     *
     * @var \Magento\Sales\Model\Order\Email\Sender
     * @since 2.0.0
     */
    protected $emailSender;

    /**
     * Entity resource model.
     *
     * @var \Magento\Sales\Model\ResourceModel\EntityAbstract
     * @since 2.0.0
     */
    protected $entityResource;

    /**
     * Entity collection model.
     *
     * @var \Magento\Sales\Model\ResourceModel\Collection\AbstractCollection
     * @since 2.0.0
     */
    protected $entityCollection;

    /**
     * Global configuration storage.
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     * @since 2.0.0
     */
    protected $globalConfig;

    /**
     * @param \Magento\Sales\Model\Order\Email\Sender $emailSender
     * @param \Magento\Sales\Model\ResourceModel\EntityAbstract $entityResource
     * @param \Magento\Sales\Model\ResourceModel\Collection\AbstractCollection $entityCollection
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $globalConfig
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Sales\Model\Order\Email\Sender $emailSender,
        \Magento\Sales\Model\ResourceModel\EntityAbstract $entityResource,
        \Magento\Sales\Model\ResourceModel\Collection\AbstractCollection $entityCollection,
        \Magento\Framework\App\Config\ScopeConfigInterface $globalConfig
    ) {
        $this->emailSender = $emailSender;
        $this->entityResource = $entityResource;
        $this->entityCollection = $entityCollection;
        $this->globalConfig = $globalConfig;
    }

    /**
     * Handles asynchronous email sending
     * @return void
     * @since 2.0.0
     */
    public function sendEmails()
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
