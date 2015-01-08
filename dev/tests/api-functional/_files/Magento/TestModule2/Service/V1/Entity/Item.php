<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\TestModule2\Service\V1\Entity;

class Item extends \Magento\Framework\Api\AbstractExtensibleObject
{
    /**
     * @return int
     */
    public function getId()
    {
        return $this->_data['id'];
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->_data['name'];
    }
}
