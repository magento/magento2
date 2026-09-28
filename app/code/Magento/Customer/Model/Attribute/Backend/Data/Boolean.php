<?php
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Customer\Model\Attribute\Backend\Data;

/**
 * Boolean customer attribute backend model
 */
class Boolean extends \Magento\Eav\Model\Entity\Attribute\Backend\AbstractBackend
{
    /**
     * Prepare data before attribute save
     *
     * @param \Magento\Customer\Model\Customer $customer
     * @return $this
     */
    public function beforeSave($customer)
    {
        $attributeName = $this->getAttribute()->getName();
        $inputValue = $customer->getData($attributeName);
        $inputValue = $inputValue === null ? $this->getAttribute()->getDefaultValue() : $inputValue;
        $sanitizedValue = !empty($inputValue) ? '1' : '0';
        $customer->setData($attributeName, $sanitizedValue);
        return $this;
    }
}
