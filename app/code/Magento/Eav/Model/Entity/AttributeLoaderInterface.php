<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Eav\Model\Entity;

use Magento\Framework\DataObject;

/**
 * Interface AttributeLoaderInterface
 */
interface AttributeLoaderInterface
{
    /**
     * Retrieve configuration for all attributes
     *
     * @param AbstractEntity $resource
     * @param DataObject|null $object
     * @return AbstractEntity
     */
    public function loadAllAttributes(AbstractEntity $resource, DataObject $object = null);
}
