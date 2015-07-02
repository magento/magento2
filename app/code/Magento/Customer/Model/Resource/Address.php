<?php
/**
 * Customer address entity resource model
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Model\Resource;

use Magento\Framework\Exception\InputException;

class Address extends \Magento\Eav\Model\Entity\VersionControl\AbstractEntity
{
    /**
     * @var \Magento\Framework\Validator\Factory
     */
    protected $_validatorFactory;

    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $_customerFactory;

    /**
     * @param \Magento\Eav\Model\Entity\Context $context
     * @param \Magento\Framework\Model\Resource\Db\VersionControl\Snapshot $entitySnapshot,
     * @param \Magento\Framework\Model\Resource\Db\VersionControl\RelationComposite $entityRelationComposite,
     * @param \Magento\Framework\Validator\Factory $validatorFactory
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Eav\Model\Entity\Context $context,
        \Magento\Framework\Model\Resource\Db\VersionControl\Snapshot $entitySnapshot,
        \Magento\Framework\Model\Resource\Db\VersionControl\RelationComposite $entityRelationComposite,
        \Magento\Framework\Validator\Factory $validatorFactory,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        $data = []
    ) {
        $this->_validatorFactory = $validatorFactory;
        $this->_customerFactory = $customerFactory;
        parent::__construct($context, $entitySnapshot, $entityRelationComposite, $data);
    }

    /**
     * Resource initialization.
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_read = 'customer_read';
        $this->_write = 'customer_write';
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
     * @param \Magento\Framework\Object $address
     * @return $this
     */
    protected function _beforeSave(\Magento\Framework\Object $address)
    {
        parent::_beforeSave($address);

        $this->_validate($address);

        return $this;
    }

    /**
     * Validate customer address entity
     *
     * @param \Magento\Framework\Object $address
     * @return void
     * @throws \Magento\Framework\Validator\Exception When validation failed
     */
    protected function _validate($address)
    {
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
     * @return \Magento\Customer\Model\Customer
     */
    protected function _createCustomer()
    {
        return $this->_customerFactory->create();
    }

    /**
     * {@inheritdoc}
     */
    public function delete($object)
    {
        $result = parent::delete($object);
        $object->setData([]);
        return $result;
    }

    /**
     * {@inheritdoc}
     */
    protected function _afterDelete(\Magento\Framework\Object $address)
    {
        if ($address->getId()) {
            $customer = $this->_createCustomer()->load($address->getCustomerId());
            if ($customer->getDefaultBilling() == $address->getId()) {
                $customer->setDefaultBilling(null);
            }
            if ($customer->getDefaultShipping() == $address->getId()) {
                $customer->setDefaultShipping(null);
            }
            $customer->save();
        }
        return parent::_afterDelete($address);
    }
}
