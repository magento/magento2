<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\TestModule2\Service\V1\Entity;

class ItemBuilder extends \Magento\Framework\Api\ExtensibleObjectBuilder
{
    /**
     * @param int $id
     * @return \Magento\TestModule2\Service\V1\Entity\ItemBuilder
     */
    public function setId($id)
    {
        $this->data['id'] = $id;
        return $this;
    }

    /**
     * @param string $name
     * @return \Magento\TestModule2\Service\V1\Entity\ItemBuilder
     */
    public function setName($name)
    {
        $this->data['name'] = $name;
        return $this;
    }
}
