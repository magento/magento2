<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\TestModule4\Service\V1\Entity;

class DataObjectRequestBuilder extends \Magento\Framework\Api\ExtensibleObjectBuilder
{
    /**
     * @param string $name
     * @return DataObjectRequest
     */
    public function setName($name)
    {
        return $this->_set('name', $name);
    }

    /**
     * @param int $entityId
     * @return DataObjectRequest
     */
    public function setEntityId($entityId)
    {
        return $this->_set('entity_id', $entityId);
    }
}
