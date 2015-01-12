<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Webapi\Service\Entity;

class DataObjectArrayBuilder extends \Magento\Framework\Api\ExtensibleObjectBuilder
{
    /**
     * @param \Magento\Webapi\Service\Entity\SimpleDataObject[] $items
     */
    public function setItems(array $items)
    {
        $this->data['items'] = $items;
    }
}
