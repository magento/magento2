<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model;

use Magento\Framework\App\ObjectManager;

/**
 * Sales emails sending
 *
 * Performs handling of cron jobs related to sending emails to customers
 * after creation/modification of Order, Invoice, Shipment or Creditmemo.
 */
class EmailSenderHandler
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
     * @var \Magento\Sales\Model\ResourceModel\EntityAbstract
     */
    protected $entityResource;

    /**
     * Entity collection model.
     *
     * @var \Magento\Sales\Model\ResourceModel\Collection\AbstractCollection
     */
    protected $entityCollection;

    /**
     * Global configuration storage.
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $globalConfig;

    /**
     * @var \Magento\Sales\Model\Order\Email\Container\IdentityInterface
     */
    private $identityContainer;

    /**
     * @param \Magento\Sales\Model\Order\Email\Sender $emailSender
     * @param \Magento\Sales\Model\ResourceModel\EntityAbstract $entityResource
     * @param \Magento\Sales\Model\ResourceModel\Collection\AbstractCollection $entityCollection
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $globalConfig
     * @param \Magento\Sales\Model\Order\Email\Container\IdentityInterface|null $identityContainer
     */
    public function __construct(
        \Magento\Sales\Model\Order\Email\Sender $emailSender,
        \Magento\Sales\Model\ResourceModel\EntityAbstract $entityResource,
        \Magento\Sales\Model\ResourceModel\Collection\AbstractCollection $entityCollection,
        \Magento\Framework\App\Config\ScopeConfigInterface $globalConfig,
        \Magento\Sales\Model\Order\Email\Container\IdentityInterface $identityContainer = null
    ) {
        $this->emailSender = $emailSender;
        $this->entityResource = $entityResource;
        $this->entityCollection = $entityCollection;
        $this->globalConfig = $globalConfig;
        $this->identityContainer = $identityContainer ?: ObjectManager::getInstance()
            ->get(\Magento\Sales\Model\Order\Email\Container\IdentityInterface::class);
    }

    /**
     * Handles asynchronous email sending
     * @return void
     */
    public function sendEmails()
    {
        if ($this->globalConfig->getValue('sales_email/general/async_sending')) {
            $this->entityCollection->addFieldToFilter('send_email', ['eq' => 1]);
            $this->entityCollection->addFieldToFilter('email_sent', ['null' => true]);

            $stores = $this->getEntityStores(clone $this->entityCollection);

            foreach ($stores as $store) {
                $this->identityContainer->setStore($store);
                if (!$this->identityContainer->isEnabled()) {
                    continue;
                }

                $this->entityCollection->addFieldToFilter('store_id', $store->getId());

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

    /**
     * Get stores for given entities.
     *
     * @param ResourceModel\Collection\AbstractCollection $entityCollection
     * @return \Magento\Store\Api\Data\StoreInterface[]
     */
    private function getEntityStores(\Magento\Sales\Model\ResourceModel\Collection\AbstractCollection $entityCollection)
    {
        $stores = [];

        $entityCollection->addAttributeToSelect('store_id')->getSelect()->group('store_id');

        /** @var \Magento\Sales\Model\EntityInterface $item */
        foreach ($entityCollection->getItems() as $item) {
            $stores []= $item->getOrder()->getStore();
        }

        return $stores;
    }
}
