<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Webapi\ServiceInputProcessor;

use Magento\Framework\Api\ExtensibleObjectBuilder;

class SimpleArrayBuilder extends ExtensibleObjectBuilder
{
    /**
     * @param array $ids
     * @return $this
     */
    public function setIds($ids)
    {
        $this->data['ids'] = $ids;
        return $this;
    }
}
