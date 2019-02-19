<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Template filter factory
 */
namespace Magento\Catalog\Model\Template\Filter;

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
     * Create template filter
     *
     * @param string $className
     * @param array $data
     * @return \Magento\Framework\Filter\Template
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function create($className, array $data = [])
    {
        $filter = $this->_objectManager->create($className, $data);

        if (!$filter instanceof \Magento\Framework\Filter\Template) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('%1 doesn\'t extend \Magento\Framework\Filter\Template', $className)
            );
        }
        return $filter;
    }
}
