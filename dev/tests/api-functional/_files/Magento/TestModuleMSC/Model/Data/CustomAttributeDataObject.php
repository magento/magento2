<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */

namespace Magento\TestModuleMSC\Model\Data;

use Magento\TestModuleMSC\Api\Data\CustomAttributeDataObjectInterface;
use Magento\Framework\Api\AbstractExtensibleObject;

/**
 * Class CustomAttributeDataObject
 *
 * @method \Magento\TestModuleMSC\Api\Data\CustomAttributeDataObjectExtensionInterface getExtensionAttributes()
 */
class CustomAttributeDataObject extends AbstractExtensibleObject implements CustomAttributeDataObjectInterface
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
