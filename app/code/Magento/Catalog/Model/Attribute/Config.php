<?php
/**
 * High-level interface for catalog attributes data that hides format from the client code
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Catalog\Model\Attribute;

class Config
{
    /**
     * @var \Magento\Catalog\Model\Attribute\Config\Data
     */
    protected $_dataStorage;

    /**
     * @param \Magento\Catalog\Model\Attribute\Config\Data $dataStorage
     */
    public function __construct(\Magento\Catalog\Model\Attribute\Config\Data $dataStorage)
    {
        $this->_dataStorage = $dataStorage;
    }

    /**
     * Retrieve names of attributes belonging to specified group
     *
     * @param string $groupName Name of an attribute group
     * @return array
     */
    public function getAttributeNames($groupName)
    {
        return $this->_dataStorage->get($groupName, []);
    }
}
