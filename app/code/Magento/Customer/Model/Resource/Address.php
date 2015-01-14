<?php
/**
 * Customer address entity resource model
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Model\Resource;

use Magento\Framework\Exception\InputException;

class Address extends \Magento\Eav\Model\Entity\AbstractEntity
{
    /**
     * @var \Magento\Core\Model\Validator\Factory
     */
    protected $_validatorFactory;

    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $_customerFactory;

    /**
     * @param \Magento\Framework\App\Resource $resource
     * @param \Magento\Eav\Model\Config $eavConfig
     * @param \Magento\Eav\Model\Entity\Attribute\Set $attrSetEntity
     * @param \Magento\Framework\Locale\FormatInterface $localeFormat
     * @param \Magento\Eav\Model\Resource\Helper $resourceHelper
     * @param \Magento\Framework\Validator\UniversalFactory $universalFactory
     * @param \Magento\Core\Model\Validator\Factory $validatorFactory
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\App\Resource $resource,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Eav\Model\Entity\Attribute\Set $attrSetEntity,
        \Magento\Framework\Locale\FormatInterface $localeFormat,
        \Magento\Eav\Model\Resource\Helper $resourceHelper,
        \Magento\Framework\Validator\UniversalFactory $universalFactory,
        \Magento\Core\Model\Validator\Factory $validatorFactory,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        $data = []
    ) {
        $this->_validatorFactory = $validatorFactory;
        $this->_customerFactory = $customerFactory;
        parent::__construct(
            $resource,
            $eavConfig,
            $attrSetEntity,
            $localeFormat,
            $resourceHelper,
            $universalFactory,
            $data
        );
    }

    /**
     * Resource initialization.
     *
     * @return void
     */
    protected function _construct()
    {
        $resource = $this->_resource;
        $this->setType(
            'customer_address'
        )->setConnection(
            $resource->getConnection('customer_read'),
            $resource->getConnection('customer_write')
        );
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
     * @throws \Magento\Framework\Validator\ValidatorException When validation failed
     */
    protected function _validate($address)
    {
        $validator = $this->_validatorFactory->createValidator('customer_address', 'save');

        if (!$validator->isValid($address)) {
            throw new \Magento\Framework\Validator\ValidatorException(
                InputException::DEFAULT_MESSAGE,
                [],
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
