<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Model\Attribute\Backend\Data;

/**
 * Boolean customer attribute backend model
 *
 * @author     Magento Core Team <core@magentocommerce.com>
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
        $inputValue = is_null($inputValue) ? $this->getAttribute()->getDefaultValue() : $inputValue;
        $sanitizedValue = !empty($inputValue) ? '1' : '0';
        $customer->setData($attributeName, $sanitizedValue);
        return $this;
    }
}
