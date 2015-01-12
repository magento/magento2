<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\TestModule1\Service\V1\Entity;

class CustomAttributeDataObjectBuilder extends \Magento\Framework\Api\ExtensibleObjectBuilder
{
    /**
     * @param string $name
     *
     * @return $this
     */
    public function setName($name)
    {
        $this->data['name'] = $name;
        return $this;
    }
}
