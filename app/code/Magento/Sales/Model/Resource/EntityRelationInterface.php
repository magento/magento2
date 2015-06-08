<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Model\Resource;

/**
 * Interface EntityRelationInterface
 */
interface EntityRelationInterface
{
    public function processRelation(\Magento\Sales\Model\AbstractModel $object);
}
