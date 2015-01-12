<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Webapi\Service\Entity;

use Magento\Framework\Api\ExtensibleObjectBuilder;

class NestedBuilder extends ExtensibleObjectBuilder
{
    /**
     * @param string $details
     * @return $this
     */
    public function setDetails($details)
    {
        $this->data['details'] = $details;
        return $this;
    }
}
