<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */

namespace Magento\TestModuleMSC\Model\Data;

use Magento\TestModuleMSC\Api\Data\CustomAttributeNestedDataObjectInterface;

/**
 * Class CustomAttributeNestedDataObject
 *
 * @method \Magento\TestModuleMSC\Api\Data\CustomAttributeNestedDataObjectExtensionInterface getExtensionAttributes()
 */
class CustomAttributeNestedDataObject extends \Magento\Framework\Model\AbstractExtensibleModel implements
    CustomAttributeNestedDataObjectInterface
{
    /**
     * @return string
     */
    public function getName()
    {
        return $this->_data['name'];
    }

    /**
     * @param string $name
     * @return $this
     */
    public function setName($name)
    {
        return $this->setData('name', $name);
    }
}
