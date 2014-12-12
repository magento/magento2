<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Webapi\Service\Entity;

use Magento\Framework\Api\ExtensibleObjectBuilder;

class DataArrayBuilder extends ExtensibleObjectBuilder
{
    /**
     * @param \Magento\Webapi\Service\Entity\Simple[] $items
     * @return $this
     */
    public function setItems($items)
    {
        $this->data['items'] = $items;
        return $this;
    }
}
