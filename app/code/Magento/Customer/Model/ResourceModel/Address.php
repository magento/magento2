<?php
/**
 * Copyright 2011 Adobe
 * All Rights Reserved.
 */

namespace Magento\Customer\Model\ResourceModel;

use Magento\Customer\Model\CustomerRegistry;
use Magento\Customer\Model\ResourceModel\Address\DeleteRelation;
use Magento\Eav\Model\ResourceModel\OrphanedMultiselectCleaner;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\DataObject;

/**
 * Customer address entity resource model
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Address extends \Magento\Eav\Model\Entity\VersionControl\AbstractEntity
{
    /**
     * @var \Magento\Framework\Validator\Factory
     */
    protected $_validatorFactory;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @var OrphanedMultiselectCleaner
     */
    private $orphanedMultiselectCleaner;

    /**
     * @var DeleteRelation
     */
    private $deleteRelation;

    /**
     * @var CustomerRegistry
     */
    private $customerRegistry;

    /**
     * @param \Magento\Eav\Model\Entity\Context $context
     * @param \Magento\Framework\Model\ResourceModel\Db\VersionControl\Snapshot $entitySnapshot
     * @param \Magento\Framework\Model\ResourceModel\Db\VersionControl\RelationComposite $entityRelationComposite
     * @param \Magento\Framework\Validator\Factory $validatorFactory
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     * @param array $data
     * @param OrphanedMultiselectCleaner|null $orphanedMultiselectCleaner
     * @param DeleteRelation|null $deleteRelation
     * @param CustomerRegistry|null $customerRegistry
     */
    public function __construct(
        \Magento\Eav\Model\Entity\Context $context,
        \Magento\Framework\Model\ResourceModel\Db\VersionControl\Snapshot $entitySnapshot,
        \Magento\Framework\Model\ResourceModel\Db\VersionControl\RelationComposite $entityRelationComposite,
        \Magento\Framework\Validator\Factory $validatorFactory,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        $data = [],
        ?OrphanedMultiselectCleaner $orphanedMultiselectCleaner = null,
        ?DeleteRelation $deleteRelation = null,
        ?CustomerRegistry $customerRegistry = null
    ) {
        $this->customerRepository = $customerRepository;
        $this->_validatorFactory = $validatorFactory;
        $this->orphanedMultiselectCleaner = $orphanedMultiselectCleaner
            ?? ObjectManager::getInstance()->get(OrphanedMultiselectCleaner::class);
        $this->deleteRelation = $deleteRelation
            ?? ObjectManager::getInstance()->get(DeleteRelation::class);
        $this->customerRegistry = $customerRegistry
            ?? ObjectManager::getInstance()->get(CustomerRegistry::class);
        parent::__construct($context, $entitySnapshot, $entityRelationComposite, $data);
    }

    /**
     * Resource initialization.
     *
     * @return void
     */
    protected function _construct()
    {
        $this->connectionName = 'customer';
    }

    /**
     * Getter and lazy loader for _type
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return \Magento\Eav\Model\Entity\Type
     */
    public function getEntityType()
    {
        if (empty($this->_type)) {
            $this->setType('customer_address');
        }
        return parent::getEntityType();
    }

    /**
     * Check customer address before saving
     *
     * @param DataObject $address
     * @return $this
     */
    protected function _beforeSave(DataObject $address)
    {
        if ($address->getId()) {
            $this->cleanOrphanedMultiselectValues($address);
        }

        parent::_beforeSave($address);

        $this->_validate($address);

        return $this;
    }

    /**
     * Validate customer address entity
     *
     * @param DataObject $address
     * @return void
     * @throws \Magento\Framework\Validator\Exception When validation failed
     */
    protected function _validate($address)
    {
        if ($address->getDataByKey('should_ignore_validation')) {
            return;
        };
        $validator = $this->_validatorFactory->createValidator('customer_address', 'save');

        if (!$validator->isValid($address)) {
            throw new \Magento\Framework\Validator\Exception(
                null,
                null,
                $validator->getMessages()
            );
        }
    }

    /**
     * @inheritdoc
     */
    public function delete($object)
    {
        $result = parent::delete($object);
        $object->setData([]);
        return $result;
    }

    /**
     * Clean up orphaned multiselect attribute values before validation
     *
     * @param DataObject $address
     * @return void
     */
    private function cleanOrphanedMultiselectValues(DataObject $address): void
    {
        $this->orphanedMultiselectCleaner->cleanEntity($this, $address);
    }

    /**
     * After delete entity process
     *
     * @param \Magento\Customer\Model\Address $address
     * @return $this
     */
    protected function _afterDelete(DataObject $address)
    {
        $customer = $this->customerRegistry->retrieve($address->getCustomerId());
        $this->deleteRelation->deleteRelation($address, $customer);
        return parent::_afterDelete($address);
    }
}
