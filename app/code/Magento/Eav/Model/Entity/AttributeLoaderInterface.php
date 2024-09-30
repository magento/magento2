<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Eav\Model\Entity;

use Magento\Framework\DataObject;

/**
 * Interface AttributeLoaderInterface
 *
 * @api
 */
interface AttributeLoaderInterface
{
    /**
     * Retrieve configuration for all attributes
     *
     * @param AbstractEntity $resource
     * @param DataObject|null $entity
     * @return AbstractEntity
     */
    public function loadAllAttributes(AbstractEntity $resource, DataObject $entity = null);
}
