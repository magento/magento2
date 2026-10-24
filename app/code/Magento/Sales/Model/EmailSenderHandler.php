<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Sales\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\ValueFactory;
use Magento\Framework\App\Config\ValueInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Sales\Model\Order\Email\Container\IdentityInterface;
use Magento\Sales\Model\Order\Email\Sender;
use Magento\Sales\Model\ResourceModel\Collection\AbstractCollection;
use Magento\Sales\Model\ResourceModel\EntityAbstract;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Lock\LockManagerInterface;

/**
 * Sales emails sending
 *
 * Performs handling of cron jobs related to sending emails to customers
 * after creation/modification of Order, Invoice, Shipment or Creditmemo.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class EmailSenderHandler
{
    /**
     * Configuration path for defining asynchronous email sending attempts
     */
    public const XML_PATH_ASYNC_SENDING_ATTEMPTS = 'sales_email/general/async_sending_attempts';

    /**
     * Configuration path for stale in-progress async email claim timeout (minutes).
     */
    public const XML_PATH_STALE_CLAIM_MINUTES = 'sales_email/general/stale_claim_minutes';

    private const LOCK_PREFIX = 'sales_async_email_';

    /**
     * Default minutes after which a stale in-progress claim may be reclaimed by another worker.
     */
    private const DEFAULT_STALE_CLAIM_MINUTES = 10;

    /**
     * email_sent value used while an entity is being processed by an async email worker.
     */
    public const EMAIL_SENT_PROCESSING = 2;

    /**
     * Email sender model.
     *
     * @var Sender
     */
    protected $emailSender;

    /**
     * Entity resource model.
     *
     * @var EntityAbstract
     */
    protected $entityResource;

    /**
     * Entity collection model.
     *
     * @var AbstractCollection
     */
    protected $entityCollection;

    /**
     * Global configuration storage.
     *
     * @var ScopeConfigInterface
     */
    protected $globalConfig;

    /**
     * @var IdentityInterface
     */
    private $identityContainer;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * Config data factory
     *
     * @var ValueFactory
     */
    private $configValueFactory;

    /**
     * @var LockManagerInterface
     */
    private $lockManager;

    /**
     * @var string
     */
    private $modifyStartFromDate;

    /**
     * @param Sender $emailSender
     * @param EntityAbstract $entityResource
     * @param AbstractCollection $entityCollection
     * @param ScopeConfigInterface $globalConfig
     * @param IdentityInterface|null $identityContainer
     * @param StoreManagerInterface|null $storeManager
     * @param ValueFactory|null $configValueFactory
     * @param string|null $modifyStartFromDate
     * @param LockManagerInterface|null $lockManager
     */
    public function __construct(
        Sender $emailSender,
        EntityAbstract $entityResource,
        AbstractCollection $entityCollection,
        ScopeConfigInterface $globalConfig,
        ?IdentityInterface $identityContainer = null,
        ?StoreManagerInterface $storeManager = null,
        ?ValueFactory $configValueFactory = null,
        ?string $modifyStartFromDate = null,
        ?LockManagerInterface $lockManager = null,
    ) {
        $this->emailSender = $emailSender;
        $this->entityResource = $entityResource;
        $this->entityCollection = $entityCollection;
        $this->globalConfig = $globalConfig;

        $this->identityContainer = $identityContainer ?: ObjectManager::getInstance()
            ->get(\Magento\Sales\Model\Order\Email\Container\NullIdentity::class);
        $this->storeManager = $storeManager ?: ObjectManager::getInstance()
            ->get(StoreManagerInterface::class);

        $this->configValueFactory = $configValueFactory ?: ObjectManager::getInstance()->get(ValueFactory::class);
        $this->lockManager = $lockManager ?: ObjectManager::getInstance()->get(LockManagerInterface::class);
        $this->modifyStartFromDate = $modifyStartFromDate ?: $this->modifyStartFromDate;
    }

    /**
     * Handles asynchronous email sending
     *
     * @return void
     */
    public function sendEmails()
    {
        if ($this->globalConfig->getValue('sales_email/general/async_sending')) {
            $this->entityCollection->addFieldToFilter('send_email', ['eq' => 1]);
            $this->addPendingEmailSentFilter($this->entityCollection);
            $this->filterCollectionByStartFromDate($this->entityCollection);
            $this->entityCollection->setPageSize(
                $this->globalConfig->getValue('sales_email/general/sending_limit')
            );

            /** @var \Magento\Store\Api\Data\StoreInterface[] $stores */
            $stores = $this->getStores(clone $this->entityCollection);

            $maxSendAttempts = $this->globalConfig->getValue(self::XML_PATH_ASYNC_SENDING_ATTEMPTS);
            $staleClaimMinutes = $this->getStaleClaimMinutes();

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
                    if (!$this->tryClaimForAsyncEmailSend((int)$item->getId(), $staleClaimMinutes)) {
                        continue;
                    }
                    $this->entityResource->load($item, $item->getId());
                    $sendAttempts = $this->resolveSendAttempts($item->getEmailSent(), $maxSendAttempts);
                    $lockName = self::LOCK_PREFIX . $this->entityResource->getMainTable() . '_' . $item->getId();
                    if (!$this->lockManager->lock($lockName, 0)) {
                        continue;
                    }
                    try {
                        $isEmailSent = $this->emailSender->send($item, true);

                        if ($isEmailSent) {
                            $sendAttempts = 1;
                        } else {
                            $sendAttempts++;
                        }

                        $this->entityResource->saveAttribute(
                            $item->setEmailSent($sendAttempts),
                            'email_sent'
                        );
                    } finally {
                        $this->lockManager->unlock($lockName);
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
        AbstractCollection $entityCollection
    ): array {
        $stores = [];

        $entityCollection->addAttributeToSelect('store_id')->getSelect()->group('store_id');
        /** @var \Magento\Sales\Model\EntityInterface $item */
        foreach ($entityCollection->getItems() as $item) {
            /** @var StoreManagerInterface $store */
            $storeId = $item->getStoreId() ?? '';
            $store = $this->storeManager->getStore($storeId);
            $stores[$storeId] = $store;
        }

        return $stores;
    }

    /**
     * Restrict collection to entities that still need async email processing.
     *
     * @param AbstractCollection $collection
     * @return void
     */
    private function addPendingEmailSentFilter(AbstractCollection $collection): void
    {
        $staleClaimMinutes = $this->getStaleClaimMinutes();
        $staleThreshold = date('Y-m-d H:i:s', strtotime(sprintf('-%d minutes', $staleClaimMinutes)));
        $collection->getSelect()->where(
            '(main_table.email_sent IS NULL OR main_table.email_sent = 0 OR main_table.email_sent <= ? '
            . 'OR (main_table.email_sent = ? AND main_table.updated_at < ?))',
            -1,
            self::EMAIL_SENT_PROCESSING,
            $staleThreshold
        );
    }

    /**
     * Resolve the retry counter for an entity pending async email delivery.
     *
     * @param mixed $emailSent
     * @param int $maxSendAttempts
     * @return int
     */
    private function resolveSendAttempts(mixed $emailSent, int $maxSendAttempts): int
    {
        if ($emailSent === null || (int)$emailSent === self::EMAIL_SENT_PROCESSING) {
            return -$maxSendAttempts;
        }

        return (int)$emailSent;
    }

    /**
     * Atomically claim an entity for async email sending.
     *
     * Sets email_sent to the in-progress status only when the row is still pending or has a stale claim.
     *
     * @param int $entityId
     * @param int $staleClaimMinutes
     * @return bool
     */
    private function tryClaimForAsyncEmailSend(int $entityId, int $staleClaimMinutes): bool
    {
        $connection = $this->entityResource->getConnection();
        $mainTable = $this->entityResource->getMainTable();
        $staleThreshold = date('Y-m-d H:i:s', strtotime(sprintf('-%d minutes', $staleClaimMinutes)));
        $pendingCondition = implode(
            ' OR ',
            [
                'email_sent IS NULL',
                'email_sent = 0',
                $connection->quoteInto('email_sent <= ?', -1),
                '(' . $connection->quoteInto('email_sent = ?', self::EMAIL_SENT_PROCESSING)
                    . ' AND ' . $connection->quoteInto('updated_at < ?', $staleThreshold) . ')',
            ]
        );
        $where = $connection->quoteInto($this->entityResource->getIdFieldName() . ' = ?', $entityId)
            . ' AND send_email = 1 AND (' . $pendingCondition . ')';
        $data = ['email_sent' => self::EMAIL_SENT_PROCESSING];
        if ($connection->tableColumnExists($mainTable, 'updated_at')) {
            $data['updated_at'] = gmdate('Y-m-d H:i:s');
        }
        return $connection->update($mainTable, $data, $where) === 1;
    }

    /**
     * Get configured stale claim timeout in minutes.
     *
     * @return int
     */
    private function getStaleClaimMinutes(): int
    {
        $staleClaimMinutes = $this->globalConfig->getValue(self::XML_PATH_STALE_CLAIM_MINUTES);

        if ($staleClaimMinutes === null || $staleClaimMinutes === '') {
            return self::DEFAULT_STALE_CLAIM_MINUTES;
        }

        return max(1, (int)$staleClaimMinutes);
    }

    /**
     * Filter collection by start from date
     *
     * @param AbstractCollection $collection
     * @return void
     */
    private function filterCollectionByStartFromDate(AbstractCollection $collection): void
    {
        /** @var $configValue ValueInterface */
        $configValue = $this->configValueFactory->create();
        $configValue->load('sales_email/general/async_sending', 'path');

        if ($configValue->getId()) {
            $startFromDate = date(
                'Y-m-d H:i:s',
                strtotime($configValue->getUpdatedAt() . ' ' . $this->modifyStartFromDate)
            );

            $collection->addFieldToFilter('created_at', ['from' => $startFromDate]);
        }
    }
}
