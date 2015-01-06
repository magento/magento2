<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\TestModuleMSC\Model\Data;

use Magento\TestModuleMSC\Api\Data\CustomAttributeDataObjectInterface;

class CustomAttributeDataObject extends \Magento\Framework\Api\AbstractExtensibleObject
    implements CustomAttributeDataObjectInterface
{
    /**
     * @return string
     */
    public function getName()
    {
        return $this->_data['name'];
    }
}
