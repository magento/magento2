<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\TestModule4\Service\V1\Entity;

class NestedDataObjectRequestBuilder extends \Magento\Framework\Api\ExtensibleObjectBuilder
{
    /**
     * @param \Magento\TestModule4\Service\V1\Entity\DataObjectRequest $details
     * @return \Magento\TestModule4\Service\V1\Entity\DataObjectRequest
     */
    public function setDetails(DataObjectRequest $details)
    {
        return $this->_set('details', $details);
    }
}
