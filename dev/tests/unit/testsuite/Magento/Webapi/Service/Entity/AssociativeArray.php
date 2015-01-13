<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Webapi\Service\Entity;

use Magento\Framework\Api\AbstractExtensibleObject;

class AssociativeArray extends AbstractExtensibleObject
{
    /**
     * @return string[]
     */
    public function getAssociativeArray()
    {
        return $this->_get('associativeArray');
    }
}
