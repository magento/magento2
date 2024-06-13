<?php
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
namespace Magento\Customer\Model\Config\Backend\Show;

use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;

/**
 * Customer Show Address Model
 */
class AddressOnly extends Customer
{
    /**
     * Retrieve attribute objects
     *
     * @return AbstractAttribute[]
     */
    protected function _getAttributeObjects()
    {
        return [$this->_eavConfig->getAttribute('customer_address', $this->_getAttributeCode())];
    }
}
