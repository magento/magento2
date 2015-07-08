<?php
/**
 * Customer address entity resource model
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Model\Resource;

class Address extends \Magento\Eav\Model\Entity\AbstractEntity
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
     * @param \Magento\Framework\Validator\Factory $validatorFactory
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     * @param array $data
     */
    public function __construct(
        \Magento\Eav\Model\Entity\Context $context,
        \Magento\Framework\Validator\Factory $validatorFactory,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        $data = []
    ) {
        $this->customerRepository = $customerRepository;
        $this->_validatorFactory = $validatorFactory;
        parent::__construct($context, $data);
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
     * Set default shipping to address
     *
     * @param \Magento\Framework\Object $address
     * @return $this
     */
    protected function _afterSave(\Magento\Framework\Object $address)
    {
        if ($address->getIsCustomerSaveTransaction()) {
            return $this;
        }
        if ($address->getId() && ($address->getIsDefaultBilling() || $address->getIsDefaultShipping())) {
            $customer = $this->_createCustomer()->load($address->getCustomerId());

            if ($address->getIsDefaultBilling()) {
                $customer->setDefaultBilling($address->getId());
            }
            if ($address->getIsDefaultShipping()) {
                $customer->setDefaultShipping($address->getId());
            }
            $customer->save();
        }
        return $this;
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
            $customer = $this->customerRepository->getById($address->getCustomerId());
            if ($customer->getDefaultBilling() == $address->getId()) {
                $customer->setDefaultBilling(null);
            }
            if ($customer->getDefaultShipping() == $address->getId()) {
                $customer->setDefaultShipping(null);
            }
            $this->customerRepository->save($customer);
        }
        return parent::_afterDelete($address);
    }
}
