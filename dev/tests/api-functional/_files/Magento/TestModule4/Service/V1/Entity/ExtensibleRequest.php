<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\TestModule4\Service\V1\Entity;

/**
 * Class ExtensibleRequest
 *
 * @method \Magento\TestModule4\Service\V1\Entity\ExtensibleRequestExtensionInterface getExtensionAttributes()
 */
class ExtensibleRequest extends \Magento\Framework\Model\AbstractExtensibleModel implements ExtensibleRequestInterface
{
    public function getName()
    {
        return $this->getData("name");
    }

    /**
     * @param string $name
     * @return $this
     */
    public function setName($name)
    {
        return $this->setData("name", $name);
    }

    /**
     * @param int $entityId
     * @return $this
     */
    public function setEntityId($entityId)
    {
        return $this->setData("entity_id", $entityId);
    }
}
