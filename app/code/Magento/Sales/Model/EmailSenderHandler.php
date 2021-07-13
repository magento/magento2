<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Model;

use Exception;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\ValueFactory;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Sales\Model\Order\Email\Container\IdentityInterface;
use Magento\Sales\Model\Order\Email\Container\NullIdentity;
use Magento\Sales\Model\Order\Email\Sender;
use Magento\Sales\Model\ResourceModel\Collection\AbstractCollection;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Sales emails sending
 *
 * Performs handling of cron jobs related to sending emails to customers
 * after creation/modification of Order, Invoice, Shipment or CreditMemo.
 */
class EmailSenderHandler
{
    /** @var Sender $emailSender */
    private $emailSender;

    /** @var AbstractCollection $entityCollection */
    private $entityCollection;

    /** @var ScopeConfigInterface $globalConfig */
    private $globalConfig;

    /** @var DateTime|null $dateTime */
    private $dateTime;

    /** @var IdentityInterface|NullIdentity|null $identityContainer  */
    private $identityContainer;

    /** @var StoreManagerInterface|null $storeManager */
    private $storeManager;

    /** @var ValueFactory|null $configValueFactory */
    private $configValueFactory;

    /** @var string|null $modifyStartFromDate */
    private $modifyStartFromDate = null;

    /**
     * @param Sender $emailSender
     * @param AbstractCollection $entityCollection
     * @param ScopeConfigInterface $globalConfig
     * @param IdentityInterface|null $identityContainer
     * @param StoreManagerInterface|null $storeManager
     * @param ValueFactory|null $configValueFactory
     * @param string|null $modifyStartFromDate
     * @param DateTime|null $dateTime
     */
    public function __construct(
        Sender $emailSender,
        AbstractCollection $entityCollection,
        ScopeConfigInterface $globalConfig,
        IdentityInterface $identityContainer = null,
        StoreManagerInterface $storeManager = null,
        ?ValueFactory $configValueFactory = null,
        ?string $modifyStartFromDate = null,
        ?DateTime $dateTime = null
    ) {
        $this->emailSender = $emailSender;
        $this->entityCollection = $entityCollection;
        $this->globalConfig = $globalConfig;
        $this->identityContainer = $identityContainer ?: ObjectManager::getInstance()->get(NullIdentity::class);
        $this->storeManager = $storeManager ?: ObjectManager::getInstance()->get(StoreManagerInterface::class);
        $this->configValueFactory = $configValueFactory ?: ObjectManager::getInstance()->get(ValueFactory::class);
        $this->modifyStartFromDate = $modifyStartFromDate ?: $this->modifyStartFromDate;
        $this->dateTime = $dateTime ?: ObjectManager::getInstance()->get(DateTime::class);
    }

    /**
     * Handles asynchronous email sending.
     *
     * @return void
     * @throws Exception
     */
    public function sendEmails(): void
    {
        if (!$this->globalConfig->isSetFlag('sales_email/general/async_sending')) {
            return;
        }

        $this->entityCollection->addFieldToFilter('send_email', ['eq' => true]);
        $this->entityCollection->addFieldToFilter('email_sent', ['null' => true]);
        $this->filterCollectionByStartFromDate($this->entityCollection);
        $this->entityCollection->setPageSize((int) $this->globalConfig->getValue('sales_email/general/sending_limit'));

        foreach ($this->storeManager->getStores() as $store) {
            $this->identityContainer->setStore($store);

            if (!$this->identityContainer->isEnabled()) {
                continue;
            }

            $entityCloneByStoreCollection = clone $this->entityCollection;
            $entityCloneByStoreCollection->addFieldToFilter('store_id', $store->getId());

            foreach ($entityCloneByStoreCollection->getItems() as $item) {
                $this->emailSender->send($item, true);
            }
        }
    }

    /**
     * Filter collection by start from date
     *
     * @param AbstractCollection $collection
     *
     * @return void
     */
    private function filterCollectionByStartFromDate(AbstractCollection $collection): void
    {
        $configValue = $this->configValueFactory->create();
        $configValue->load('sales_email/general/async_sending', 'path');

        if (!$configValue->getId()) {
            return;
        }

        $startFromDate = $this->dateTime->date(
            'Y-m-d H:i:s',
            strtotime($configValue->getUpdatedAt() . ' ' . $this->modifyStartFromDate)
        );

        $collection->addFieldToFilter('created_at', ['gteq' => $startFromDate]);
    }
}
