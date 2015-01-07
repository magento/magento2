<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
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
