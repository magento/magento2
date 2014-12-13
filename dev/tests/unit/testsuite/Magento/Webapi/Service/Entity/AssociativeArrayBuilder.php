<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Webapi\Service\Entity;

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
