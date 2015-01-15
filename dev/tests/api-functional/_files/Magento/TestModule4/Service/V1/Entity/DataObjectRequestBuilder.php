<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
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
