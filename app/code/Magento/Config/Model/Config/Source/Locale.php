<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */

/**
 * Locale source
 */
namespace Magento\Config\Model\Config\Source;

/**
 * @api
 * @since 100.0.2
 */
class Locale implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @var \Magento\Framework\Locale\ListsInterface
     */
    protected $_localeLists;

    /**
     * @param \Magento\Framework\Locale\ListsInterface $localeLists
     */
    public function __construct(\Magento\Framework\Locale\ListsInterface $localeLists)
    {
        $this->_localeLists = $localeLists;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return $this->_localeLists->getOptionLocales();
    }
}
