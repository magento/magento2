<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Webapi\ServiceInputProcessor;

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
     * @return string|null
     */
    public function getName()
    {
        return $this->_get('name');
    }
}
