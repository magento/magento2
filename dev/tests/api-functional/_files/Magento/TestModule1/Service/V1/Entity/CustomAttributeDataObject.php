<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
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
