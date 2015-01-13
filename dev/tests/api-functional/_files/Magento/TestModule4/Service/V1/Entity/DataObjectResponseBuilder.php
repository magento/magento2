<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\TestModule4\Service\V1\Entity;

class DataObjectResponseBuilder extends \Magento\Framework\Api\ExtensibleObjectBuilder
{
    /**
     * @param int $entityId
     * @return DataObjectResponseBuilder
     */
    public function setEntityId($entityId)
    {
        return $this->_set('entity_id', $entityId);
    }

    /**
     * @param string $name
     * @return DataObjectResponseBuilder
     */
    public function setName($name)
    {
        return $this->_set('name', $name);
    }
}
