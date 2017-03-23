<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Layer filter factory
 */
namespace Magento\Catalog\Model\Layer\Filter;

class Factory
{
    /**
     * Object Manager
     *
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * Construct
     *
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->_objectManager = $objectManager;
    }

    /**
     * Create layer filter
     *
     * @param string $className
     * @param array $data
     * @return \Magento\Catalog\Model\Layer\Filter\Attribute
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function create($className, array $data = [])
    {
        $filter = $this->_objectManager->create($className, $data);

        if (!$filter instanceof \Magento\Catalog\Model\Layer\Filter\AbstractFilter) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('%1 doesn\'t extends \Magento\Catalog\Model\Layer\Filter\AbstractFilter', $className)
            );
        }
        return $filter;
    }
}
