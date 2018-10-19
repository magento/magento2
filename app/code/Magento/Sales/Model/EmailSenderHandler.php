<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model;

use Magento\Sales\Model\Order\Email\Container\IdentityInterface;

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
     * @var IdentityInterface
     */
    private $identityContainer;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param \Magento\Sales\Model\Order\Email\Sender $emailSender
     * @param \Magento\Sales\Model\ResourceModel\EntityAbstract $entityResource
     * @param \Magento\Sales\Model\ResourceModel\Collection\AbstractCollection $entityCollection
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $globalConfig
     * @param IdentityInterface|null $identityContainer
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @throws \InvalidArgumentException
     */
    public function __construct(
        \Magento\Sales\Model\Order\Email\Sender $emailSender,
        \Magento\Sales\Model\ResourceModel\EntityAbstract $entityResource,
        \Magento\Sales\Model\ResourceModel\Collection\AbstractCollection $entityCollection,
        \Magento\Framework\App\Config\ScopeConfigInterface $globalConfig,
        IdentityInterface $identityContainer = null,
        \Magento\Store\Model\StoreManagerInterface $storeManager = null
    ) {
        $this->emailSender = $emailSender;
        $this->entityResource = $entityResource;
        $this->entityCollection = $entityCollection;
        $this->globalConfig = $globalConfig;

        $this->identityContainer = $identityContainer ?: \Magento\Framework\App\ObjectManager::getInstance()
            ->get(\Magento\Sales\Model\Order\Email\Container\NullIdentity::class);
        $this->storeManager = $storeManager ?: \Magento\Framework\App\ObjectManager::getInstance()
            ->get(\Magento\Store\Model\StoreManagerInterface::class);
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
            $this->entityCollection->setPageSize(
                $this->globalConfig->getValue('sales_email/general/sending_limit')
            );

            /** @var \Magento\Store\Api\Data\StoreInterface[] $stores */
            $stores = $this->getStores(clone $this->entityCollection);

            /** @var \Magento\Store\Model\Store $store */
            foreach ($stores as $store) {
                $this->identityContainer->setStore($store);
                if (!$this->identityContainer->isEnabled()) {
                    continue;
                }
                $entityCollection = clone $this->entityCollection;
                $entityCollection->addFieldToFilter('store_id', $store->getId());

                /** @var \Magento\Sales\Model\AbstractModel $item */
                foreach ($entityCollection->getItems() as $item) {
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
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function getStores(
        \Magento\Sales\Model\ResourceModel\Collection\AbstractCollection $entityCollection
    ): array {
        $stores = [];

        $entityCollection->addAttributeToSelect('store_id')->getSelect()->group('store_id');
        /** @var \Magento\Sales\Model\EntityInterface $item */
        foreach ($entityCollection->getItems() as $item) {
            /** @var \Magento\Store\Model\StoreManagerInterface $store */
            $store = $this->storeManager->getStore($item->getStoreId());
            $stores[$item->getStoreId()] = $store;
        }

        return $stores;
    }
}
