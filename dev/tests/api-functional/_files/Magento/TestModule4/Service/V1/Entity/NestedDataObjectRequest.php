<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\TestModule4\Service\V1\Entity;

class NestedDataObjectRequest extends \Magento\Framework\Api\AbstractExtensibleObject
{
    /**
     * @return \Magento\TestModule4\Service\V1\Entity\DataObjectRequest
     */
    public function getDetails()
    {
        return $this->_get('details');
    }
}
