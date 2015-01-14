<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
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
