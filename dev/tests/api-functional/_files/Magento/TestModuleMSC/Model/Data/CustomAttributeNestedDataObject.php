<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\TestModuleMSC\Model\Data;

use Magento\TestModuleMSC\Api\Data\CustomAttributeNestedDataObjectInterface;

class CustomAttributeNestedDataObject extends \Magento\Framework\Model\AbstractExtensibleModel
    implements CustomAttributeNestedDataObjectInterface
{
    /**
     * @return string
     */
    public function getName()
    {
        return $this->_data['name'];
    }
}
