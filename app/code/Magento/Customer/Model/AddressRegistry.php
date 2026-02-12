<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */

namespace Magento\Customer\Model;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\ObjectManager\ResetAfterRequestInterface;

/**
 * Registry for Address models
 */
class AddressRegistry implements ResetAfterRequestInterface
{
    /**
     * @var Address[]
     */
    protected $registry = [];

    /**
     * @var AddressFactory
     */
    protected $addressFactory;

    /**
     * @param AddressFactory $addressFactory
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
     */
    public function retrieve($addressId)
    {
        $registryKey = (string)$addressId;
        if (isset($this->registry[$registryKey])) {
            return $this->registry[$registryKey];
        }
        $address = $this->addressFactory->create();
        $address->load($addressId);
        if (!$address->getId()) {
            throw NoSuchEntityException::singleField('addressId', $addressId);
        }
        $this->registry[$registryKey] = $address;
        return $address;
    }

    /**
     * Remove an instance of the Address Model from the registry
     *
     * @param int $addressId
     * @return void
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
     */
    public function push(Address $address)
    {
        $this->registry[$address->getId()] = $address;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function _resetState(): void
    {
        $this->registry = [];
    }
}
