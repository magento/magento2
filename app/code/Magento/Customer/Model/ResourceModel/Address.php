<?php
/**
 * Customer address entity resource model
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Model\ResourceModel;

use Magento\Customer\Controller\Adminhtml\Group\Delete;
use Magento\Customer\Model\CustomerRegistry;
use Magento\Customer\Model\ResourceModel\Address\DeleteRelation;
use Magento\Framework\App\ObjectManager;

/**
 * Class Address
 *
 * @package Magento\Customer\Model\ResourceModel
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
     * @param \Magento\Eav\Model\Entity\Context $context
     * @param \Magento\Framework\Model\ResourceModel\Db\VersionControl\Snapshot $entitySnapshot
     * @param \Magento\Framework\Model\ResourceModel\Db\VersionControl\RelationComposite $entityRelationComposite
     * @param \Magento\Framework\Validator\Factory $validatorFactory
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     * @param array $data
     */
    public function __construct(
        \Magento\Eav\Model\Entity\Context $context,
        \Magento\Framework\Model\ResourceModel\Db\VersionControl\Snapshot $entitySnapshot,
        \Magento\Framework\Model\ResourceModel\Db\VersionControl\RelationComposite $entityRelationComposite,
        \Magento\Framework\Validator\Factory $validatorFactory,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        $data = []
    ) {
        $this->customerRepository = $customerRepository;
        $this->_validatorFactory = $validatorFactory;
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
     * @param \Magento\Framework\DataObject $address
     * @return $this
     */
    protected function _beforeSave(\Magento\Framework\DataObject $address)
    {
        parent::_beforeSave($address);

        $this->_validate($address);

        return $this;
    }

    /**
     * Validate customer address entity
     *
     * @param \Magento\Framework\DataObject $address
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
     * Get instance of DeleteRelation class
     *
     * @deprecated 101.0.0
     * @return DeleteRelation
     */
    private function getDeleteRelation()
    {
        return ObjectManager::getInstance()->get(DeleteRelation::class);
    }

    /**
     * Get instance of CustomerRegistry class
     *
     * @deprecated 101.0.0
     * @return CustomerRegistry
     */
    private function getCustomerRegistry()
    {
        return ObjectManager::getInstance()->get(CustomerRegistry::class);
    }

    /**
     * After delete entity process
     *
     * @param \Magento\Customer\Model\Address $address
     * @return $this
     */
    protected function _afterDelete(\Magento\Framework\DataObject $address)
    {
        $customer = $this->getCustomerRegistry()->retrieve($address->getCustomerId());

        $this->getDeleteRelation()->deleteRelation($address, $customer);
        return parent::_afterDelete($address);
    }
}
