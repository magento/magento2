<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
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
