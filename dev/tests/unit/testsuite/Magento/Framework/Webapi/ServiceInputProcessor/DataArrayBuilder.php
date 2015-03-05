<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Webapi\ServiceInputProcessor;

use Magento\Framework\Api\ExtensibleObjectBuilder;

class DataArrayBuilder extends ExtensibleObjectBuilder
{
    /**
     * @param \Magento\Framework\Webapi\ServiceInputProcessor\Simple[] $items
     * @return $this
     */
    public function setItems($items)
    {
        $this->data['items'] = $items;
        return $this;
    }
}
