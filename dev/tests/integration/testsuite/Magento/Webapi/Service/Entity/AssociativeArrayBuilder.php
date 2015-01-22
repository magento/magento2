<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Webapi\Service\Entity;

class AssociativeArrayBuilder extends \Magento\Framework\Api\ExtensibleObjectBuilder
{
    /**
     * @param string[] $associativeArray
     */
    public function setAssociativeArray(array $associativeArray)
    {
        $this->data['associativeArray'] = $associativeArray;
    }
}
