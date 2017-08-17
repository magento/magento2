<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Webapi\Test\Unit\ServiceInputProcessor;

use Magento\Framework\Api\AbstractExtensibleObject;

class Simple extends AbstractExtensibleObject
{
    /**
     * @return int|null
     */
    public function getEntityId()
    {
        return $this->_get('entityId');
    }

    /**
     * @param int $entityId
     * @return $this
     */
    public function setEntityId($entityId)
    {
        return $this->setData('entityId', $entityId);
    }

    /**
     * @return string|null
     */
    public function getName()
    {
        return $this->_get('name');
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
