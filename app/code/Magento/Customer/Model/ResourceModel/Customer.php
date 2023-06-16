<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Model\ResourceModel;

use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Model\AccountConfirmation;
use Magento\Customer\Model\Customer\NotificationStorage;
use Magento\Eav\Model\Entity\Context;
use Magento\Eav\Model\Entity\VersionControl\AbstractEntity;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\DataObject;
use Magento\Framework\DB\Select;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Model\ResourceModel\Db\VersionControl\RelationComposite;
use Magento\Framework\Model\ResourceModel\Db\VersionControl\Snapshot;
use Magento\Framework\Stdlib\DateTime;
use Magento\Framework\Validator\Exception as ValidatorException;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Validator\Factory;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Customer entity resource model
 *
 * @api
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @since 100.0.2
 */
class Customer extends AbstractEntity
{
    /**
     * @var Factory
     */
    protected $_validatorFactory;

    /**
     * Core store config
     *
     * @var ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var DateTime
     */
    protected $dateTime;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var AccountConfirmation
     */
    private $accountConfirmation;

    /**
     * @var NotificationStorage
     */
    private $notificationStorage;

    /**
     * @var EncryptorInterface
     */
    private $encryptor;

    /**
     * Customer constructor.
     *
     * @param Context $context
     * @param Snapshot $entitySnapshot
     * @param RelationComposite $entityRelationComposite
     * @param ScopeConfigInterface $scopeConfig
     * @param Factory $validatorFactory
     * @param DateTime $dateTime
     * @param StoreManagerInterface $storeManager
     * @param array $data
     * @param AccountConfirmation|null $accountConfirmation
     * @param EncryptorInterface|null $encryptor
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        Context $context,
        Snapshot $entitySnapshot,
        RelationComposite $entityRelationComposite,
        ScopeConfigInterface $scopeConfig,
        Factory $validatorFactory,
        DateTime $dateTime,
        StoreManagerInterface $storeManager,
        $data = [],
        AccountConfirmation $accountConfirmation = null,
        EncryptorInterface $encryptor = null
    ) {
        parent::__construct($context, $entitySnapshot, $entityRelationComposite, $data);

        $this->_scopeConfig = $scopeConfig;
        $this->_validatorFactory = $validatorFactory;
        $this->dateTime = $dateTime;
        $this->accountConfirmation = $accountConfirmation ?: ObjectManager::getInstance()
            ->get(AccountConfirmation::class);
        $this->setType('customer');
        $this->setConnection('customer_read');
        $this->storeManager = $storeManager;
        $this->encryptor = $encryptor ?? ObjectManager::getInstance()
                ->get(EncryptorInterface::class);
    }

    /**
     * Retrieve customer entity default attributes
     *
     * @return string[]
     */
    protected function _getDefaultAttributes()
    {
        return [
            'created_at',
            'updated_at',
            'increment_id',
            'store_id',
            'website_id'
        ];
    }

    /**
     * Check customer scope, email and confirmation key before saving
     *
     * @param DataObject|CustomerInterface $customer
     *
     * @return $this
     * @throws AlreadyExistsException
     * @throws ValidatorException
     * @throws NoSuchEntityException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function _beforeSave(DataObject $customer)
    {
        /** @var \Magento\Customer\Model\Customer $customer */
        if ($customer->getStoreId() === null) {
            $customer->setStoreId($this->storeManager->getStore()->getId());
        }
        $customer->getGroupId();

        parent::_beforeSave($customer);

        if (!$customer->getEmail()) {
            throw new ValidatorException(__('The customer email is missing. Enter and try again.'));
        }

        $connection = $this->getConnection();
        $bind = ['email' => $customer->getEmail()];

        $select = $connection->select()->from(
            $this->getEntityTable(),
            [$this->getEntityIdField()]
        )->where(
            'email = :email'
        );
        if ($customer->getSharingConfig()->isWebsiteScope()) {
            $bind['website_id'] = (int)$customer->getWebsiteId();
            $select->where('website_id = :website_id');
        }
        if ($customer->getId()) {
            $bind['entity_id'] = (int)$customer->getId();
            $select->where('entity_id != :entity_id');
        }

        $result = $connection->fetchOne($select, $bind);
        if ($result) {
            throw new AlreadyExistsException(
                __('A customer with the same email address already exists in an associated website.')
            );
        }

        // set confirmation key logic
        if ($this->isConfirmationRequired($customer)) {
            $customer->setConfirmation($customer->getRandomConfirmationKey());
        }
        // remove customer confirmation key from database, if empty
        if (!$customer->getConfirmation()) {
            $customer->setConfirmation(null);
        }

        if (!$customer->getData('ignore_validation_flag')) {
            $this->_validate($customer);
        }

        if ($customer->getData('rp_token')) {
            $rpToken = $customer->getData('rp_token');
            $customer->setRpToken($this->encryptor->encrypt($rpToken));
        }

        return $this;
    }

    /**
     * Checks if customer email verification is required
     *
     * @param DataObject|CustomerInterface $customer
     * @return bool
     */
    private function isConfirmationRequired(DataObject $customer): bool
    {
        return $this->isNewCustomerConfirmationRequired($customer)
            || $this->isExistingCustomerConfirmationRequired($customer);
    }

    /**
     * Checks if customer email verification is required for a new customer
     *
     * @param DataObject|CustomerInterface $customer
     * @return bool
     */
    private function isNewCustomerConfirmationRequired(DataObject $customer): bool
    {
        return !$customer->getId()
            && $this->accountConfirmation->isConfirmationRequired(
                $customer->getWebsiteId(),
                $customer->getId(),
                $customer->getEmail()
            );
    }

    /**
     * Checks if customer email verification is required for an existing customer
     *
     * @param DataObject|CustomerInterface $customer
     * @return bool
     */
    private function isExistingCustomerConfirmationRequired(DataObject $customer): bool
    {
         return $customer->getId()
             && $customer->dataHasChangedFor('email')
             && $this->accountConfirmation->isEmailChangedConfirmationRequired(
                 (int)$customer->getWebsiteId(),
                 (int)$customer->getId(),
                 $customer->getEmail()
             );
    }

    /**
     * Validate customer entity
     *
     * @param \Magento\Customer\Model\Customer $customer
     * @return void
     * @throws ValidatorException
     */
    protected function _validate($customer)
    {
        $validator = $this->_validatorFactory->createValidator('customer', 'save');

        if (!$validator->isValid($customer)) {
            throw new ValidatorException(
                null,
                null,
                $validator->getMessages()
            );
        }
    }

    /**
     * Retrieve notification storage
     *
     * @return NotificationStorage
     */
    private function getNotificationStorage()
    {
        if ($this->notificationStorage === null) {
            $this->notificationStorage = ObjectManager::getInstance()->get(NotificationStorage::class);
        }
        return $this->notificationStorage;
    }

    /**
     * Save customer addresses and set default addresses in attributes backend
     *
     * @param DataObject $customer
     * @return $this
     */
    protected function _afterSave(DataObject $customer)
    {
        $this->getNotificationStorage()->add(
            NotificationStorage::UPDATE_CUSTOMER_SESSION,
            $customer->getId()
        );
        if ($customer->getData('rp_token')) {
            $rpToken = $customer->getData('rp_token');
            $customer->setRpToken($this->encryptor->decrypt($rpToken));
        }
        return parent::_afterSave($customer);
    }

    /**
     * Retrieve select object for loading base entity row
     *
     * @param DataObject $object
     * @param string|int $rowId
     * @return Select
     */
    protected function _getLoadRowSelect($object, $rowId)
    {
        $select = parent::_getLoadRowSelect($object, $rowId);
        if ($object->getWebsiteId() && $object->getSharingConfig()->isWebsiteScope()) {
            $select->where('website_id =?', (int)$object->getWebsiteId());
        }

        return $select;
    }

    /**
     * Load customer by email
     *
     * @param \Magento\Customer\Model\Customer $customer
     * @param string $email
     * @return $this
     * @throws LocalizedException
     */
    public function loadByEmail(\Magento\Customer\Model\Customer $customer, $email)
    {
        $connection = $this->getConnection();
        $bind = ['customer_email' => $email];
        $select = $connection->select()->from(
            $this->getEntityTable(),
            [$this->getEntityIdField()]
        )->where(
            'email = :customer_email'
        );

        if ($customer->getSharingConfig()->isWebsiteScope()) {
            if (!$customer->hasData('website_id')) {
                throw new LocalizedException(
                    __("A customer website ID wasn't specified. The ID must be specified to use the website scope.")
                );
            }
            $bind['website_id'] = (int)$customer->getWebsiteId();
            $select->where('website_id = :website_id');
        }

        $customerId = $connection->fetchOne($select, $bind);
        if ($customerId) {
            $this->load($customer, $customerId);
        } else {
            $customer->setData([]);
        }

        return $this;
    }

    /**
     * Change customer password
     *
     * @param \Magento\Customer\Model\Customer $customer
     * @param string $newPassword
     * @return $this
     */
    public function changePassword(\Magento\Customer\Model\Customer $customer, $newPassword)
    {
        $customer->setPassword($newPassword);
        return $this;
    }

    /**
     * Check whether there are email duplicates of customers in global scope
     *
     * @return bool
     */
    public function findEmailDuplicates()
    {
        $connection = $this->getConnection();
        $select = $connection->select()->from(
            $this->getTable('customer_entity'),
            ['email', 'cnt' => 'COUNT(*)']
        )->group(
            'email'
        )->order(
            'cnt DESC'
        )->limit(
            1
        );
        $lookup = $connection->fetchRow($select);
        if (empty($lookup)) {
            return false;
        }
        return $lookup['cnt'] > 1;
    }

    /**
     * Check customer by id
     *
     * @param int $customerId
     * @return bool
     */
    public function checkCustomerId($customerId)
    {
        $connection = $this->getConnection();
        $bind = ['entity_id' => (int)$customerId];
        $select = $connection->select()->from(
            $this->getTable('customer_entity'),
            'entity_id'
        )->where(
            'entity_id = :entity_id'
        )->limit(
            1
        );

        $result = $connection->fetchOne($select, $bind);
        if ($result) {
            return true;
        }
        return false;
    }

    /**
     * Get customer website id
     *
     * @param int $customerId
     * @return int
     */
    public function getWebsiteId($customerId)
    {
        $connection = $this->getConnection();
        $bind = ['entity_id' => (int)$customerId];
        $select = $connection->select()->from(
            $this->getTable('customer_entity'),
            'website_id'
        )->where(
            'entity_id = :entity_id'
        );

        return $connection->fetchOne($select, $bind);
    }

    /**
     * Custom setter of increment ID if its needed
     *
     * @param DataObject $object
     * @return $this
     */
    public function setNewIncrementId(DataObject $object)
    {
        if ($this->_scopeConfig->getValue(
            \Magento\Customer\Model\Customer::XML_PATH_GENERATE_HUMAN_FRIENDLY_ID,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        )
        ) {
            parent::setNewIncrementId($object);
        }
        return $this;
    }

    /**
     * Change reset password link token
     *
     * Stores new reset password link token and its creation time
     *
     * @param \Magento\Customer\Model\Customer $customer
     * @param string $passwordLinkToken
     * @return $this
     */
    public function changeResetPasswordLinkToken(\Magento\Customer\Model\Customer $customer, $passwordLinkToken)
    {
        if (is_string($passwordLinkToken) && !empty($passwordLinkToken)) {
            $customer->setRpToken($passwordLinkToken);
            $customer->setRpTokenCreatedAt(
                (new \DateTime())->format(DateTime::DATETIME_PHP_FORMAT)
            );
        }
        return $this;
    }

    /**
     * Gets the session cut off timestamp string for provided customer id.
     *
     * @param int $customerId
     * @return int|null
     */
    public function findSessionCutOff(int $customerId): ?int
    {
        $connection = $this->getConnection();
        $select = $connection->select()->from(
            ['customer_table' => $this->getTable('customer_entity')],
            ['session_cutoff' => 'customer_table.session_cutoff']
        )->where(
            'entity_id =?',
            $customerId
        )->limit(
            1
        );
        $lookup = $connection->fetchRow($select);
        if (empty($lookup) || $lookup['session_cutoff'] == null) {
            return null;
        }
        return strtotime($lookup['session_cutoff']);
    }

    /**
     * Update session cutoff column value for customer
     *
     * @param int $customerId
     * @param int $timestamp
     * @return void
     */
    public function updateSessionCutOff(int $customerId, int $timestamp): void
    {
        $this->getConnection()->update(
            $this->getTable('customer_entity'),
            ['session_cutoff' => $this->dateTime->formatDate($timestamp)],
            $this->getConnection()->quoteInto('entity_id = ?', $customerId)
        );
    }

    /**
     * @inheritDoc
     */
    protected function _afterLoad(DataObject $customer)
    {
        if ($customer->getData('rp_token')) {
            $rpToken = $customer->getData('rp_token');
            $customer->setRpToken($this->encryptor->decrypt($rpToken));
        }
        return parent::_afterLoad($customer); //
    }
}
