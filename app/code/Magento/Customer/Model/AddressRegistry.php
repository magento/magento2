<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Model;

use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Registry for Address models
 * @since 2.0.0
 */
class AddressRegistry
{
    /**
     * @var Address[]
     * @since 2.0.0
     */
    protected $registry = [];

    /**
     * @var AddressFactory
     * @since 2.0.0
     */
    protected $addressFactory;

    /**
     * @param AddressFactory $addressFactory
     * @since 2.0.0
     */
    public function __construct(AddressFactory $addressFactory)
    {
        $this->addressFactory = $addressFactory;
    }

    /**
     * Get instance of the Address Model identified by id
     *
     * @param int $addressId
     * @return Address
     * @throws NoSuchEntityException
     * @since 2.0.0
     */
    public function retrieve($addressId)
    {
        if (isset($this->registry[$addressId])) {
            return $this->registry[$addressId];
        }
        $address = $this->addressFactory->create();
        $address->load($addressId);
        if (!$address->getId()) {
            throw NoSuchEntityException::singleField('addressId', $addressId);
        }
        $this->registry[$addressId] = $address;
        return $address;
    }

    /**
     * Remove an instance of the Address Model from the registry
     *
     * @param int $addressId
     * @return void
     * @since 2.0.0
     */
    public function remove($addressId)
    {
        unset($this->registry[$addressId]);
    }

    /**
     * Replace existing Address Model with a new one
     *
     * @param Address $address
     * @return $this
     * @since 2.0.0
     */
    public function push(Address $address)
    {
        $this->registry[$address->getId()] = $address;
        return $this;
    }
}
