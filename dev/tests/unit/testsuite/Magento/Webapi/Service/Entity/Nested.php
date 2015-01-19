<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Webapi\Service\Entity;

use Magento\Framework\Api\AbstractExtensibleObject;

class Nested extends AbstractExtensibleObject
{
    /**
     * @return \Magento\Webapi\Service\Entity\Simple
     */
    public function getDetails()
    {
        return $this->_get('details');
    }
}
