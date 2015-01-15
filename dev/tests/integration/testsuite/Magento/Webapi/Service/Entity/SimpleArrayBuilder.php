<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Webapi\Service\Entity;

class SimpleArrayBuilder extends \Magento\Framework\Api\ExtensibleObjectBuilder
{
    /**
     * @param int[] $ids
     */
    public function setIds(array $ids)
    {
        $this->data['ids'] = $ids;
    }
}
