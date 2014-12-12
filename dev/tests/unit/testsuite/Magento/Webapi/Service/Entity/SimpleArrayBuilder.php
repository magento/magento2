<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Webapi\Service\Entity;

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
