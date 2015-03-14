<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Webapi\Test\Unit\ServiceInputProcessor;

use Magento\Framework\Api\ExtensibleObjectBuilder;

class AssociativeArrayBuilder extends ExtensibleObjectBuilder
{
    /**
     * @param string[] $associativeArray
     * @return $this
     */
    public function setAssociativeArray($associativeArray)
    {
        $this->data['associativeArray'] = $associativeArray;
        return $this;
    }
}
