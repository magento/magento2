<?php
/**
 * High-level interface for catalog attributes data that hides format from the client code
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Attribute;

/**
 * Class \Magento\Catalog\Model\Attribute\Config
 *
 * @since 2.0.0
 */
class Config
{
    /**
     * @var \Magento\Catalog\Model\Attribute\Config\Data
     * @since 2.0.0
     */
    protected $_dataStorage;

    /**
     * @param \Magento\Catalog\Model\Attribute\Config\Data $dataStorage
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function getAttributeNames($groupName)
    {
        return $this->_dataStorage->get($groupName, []);
    }
}
