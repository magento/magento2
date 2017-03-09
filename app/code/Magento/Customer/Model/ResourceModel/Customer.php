<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Model\ResourceModel;

use Magento\Customer\Model\Customer\NotificationStorage;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Validator\Exception as ValidatorException;
use Magento\Framework\Exception\AlreadyExistsException;

/**
 * Customer entity resource model
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Customer extends \Magento\Eav\Model\Entity\VersionControl\AbstractEntity
{
    /**
     * @var \Magento\Framework\Validator\Factory
     */
    protected $_validatorFactory;

    /**
     * Core store config
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var \Magento\Framework\Stdlib\DateTime
     */
    protected $dateTime;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var NotificationStorage
     */
    private $notificationStorage;

    /**
     * @param \Magento\Eav\Model\Entity\Context $context
     * @param \Magento\Framework\Model\ResourceModel\Db\VersionControl\Snapshot $entitySnapshot
     * @param \Magento\Framework\Model\ResourceModel\Db\VersionControl\RelationComposite $entityRelationComposite
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\Validator\Factory $validatorFactory
     * @param \Magento\Framework\Stdlib\DateTime $dateTime
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param array $data
     */
    public function __construct(
        \Magento\Eav\Model\Entity\Context $context,
        \Magento\Framework\Model\ResourceModel\Db\VersionControl\Snapshot $entitySnapshot,
        \Magento\Framework\Model\ResourceModel\Db\VersionControl\RelationComposite $entityRelationComposite,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Validator\Factory $validatorFactory,
        \Magento\Framework\Stdlib\DateTime $dateTime,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        $data = []
    ) {
        parent::__construct($context, $entitySnapshot, $entityRelationComposite, $data);
        $this->_scopeConfig = $scopeConfig;
        $this->_validatorFactory = $validatorFactory;
        $this->dateTime = $dateTime;
        $this->storeManager = $storeManager;
        $this->setType('customer');
        $this->setConnection('customer_read', 'customer_write');
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
     * @param \Magento\Framework\DataObject $customer
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function _beforeSave(\Magento\Framework\DataObject $customer)
    {
        /** @var \Magento\Customer\Model\Customer $customer */
        if ($customer->getStoreId() === null) {
            $customer->setStoreId($this->storeManager->getStore()->getId());
        }
        $customer->getGroupId();

        parent::_beforeSave($customer);

        if (!$customer->getEmail()) {
            throw new ValidatorException(__('Please enter a customer email.'));
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
                __('A customer with the same email already exists in an associated website.')
            );
        }

        // set confirmation key logic
        if ($customer->getForceConfirmed() || $customer->getPasswordHash() == '') {
            $customer->setConfirmation(null);
        } elseif (!$customer->getId() && $customer->isConfirmationRequired()) {
            $customer->setConfirmation($customer->getRandomConfirmationKey());
        }
        // remove customer confirmation key from database, if empty
        if (!$customer->getConfirmation()) {
            $customer->setConfirmation(null);
        }

        $this->_validate($customer);

        return $this;
    }

    /**
     * Validate customer entity
     *
     * @param \Magento\Customer\Model\Customer $customer
     * @return void
     * @throws \Magento\Framework\Validator\Exception
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
     * @param \Magento\Framework\DataObject $customer
     * @return $this
     */
    protected function _afterSave(\Magento\Framework\DataObject $customer)
    {
        $this->getNotificationStorage()->add(
            NotificationStorage::UPDATE_CUSTOMER_SESSION,
            $customer->getId()
        );
        return parent::_afterSave($customer);
    }

    /**
     * Retrieve select object for loading base entity row
     *
     * @param \Magento\Framework\DataObject $object
     * @param string|int $rowId
     * @return \Magento\Framework\DB\Select
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
     * @throws \Magento\Framework\Exception\LocalizedException
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
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('A customer website ID must be specified when using the website scope.')
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
     * @param \Magento\Framework\DataObject $object
     * @return $this
     */
    public function setNewIncrementId(\Magento\Framework\DataObject $object)
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
                (new \DateTime())->format(\Magento\Framework\Stdlib\DateTime::DATETIME_PHP_FORMAT)
            );
        }
        return $this;
    }
}
