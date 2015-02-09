<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\TestModule1\Service\V1\Entity;

class CustomAttributeDataObject extends \Magento\Framework\Api\AbstractExtensibleObject
{
    /**
     * @return string
     */
    public function getName()
    {
        return $this->_data['name'];
    }
}
