<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
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
