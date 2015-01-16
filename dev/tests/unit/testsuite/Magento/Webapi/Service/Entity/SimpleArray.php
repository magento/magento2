<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Webapi\Service\Entity;

use Magento\Framework\Api\AbstractExtensibleObject;

class SimpleArray extends AbstractExtensibleObject
{
    /**
     * @return int[]
     */
    public function getIds()
    {
        return $this->_get('ids');
    }
}
