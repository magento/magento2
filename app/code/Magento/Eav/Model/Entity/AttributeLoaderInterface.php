<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Eav\Model\Entity;

use Magento\Framework\DataObject;

/**
 * Interface AttributeLoaderInterface
 * @since 2.1.0
 */
interface AttributeLoaderInterface
{
    /**
     * Retrieve configuration for all attributes
     *
     * @param AbstractEntity $resource
     * @param DataObject|null $object
     * @return AbstractEntity
     * @since 2.1.0
     */
    public function loadAllAttributes(AbstractEntity $resource, DataObject $object = null);
}
