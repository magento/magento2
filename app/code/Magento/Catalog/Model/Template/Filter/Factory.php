<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Template filter factory
 */
namespace Magento\Catalog\Model\Template\Filter;

/**
 * Class \Magento\Catalog\Model\Template\Filter\Factory
 *
 * @since 2.0.0
 */
class Factory
{
    /**
     * Object Manager
     *
     * @var \Magento\Framework\ObjectManagerInterface
     * @since 2.0.0
     */
    protected $_objectManager;

    /**
     * Construct
     *
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @since 2.0.0
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
     * @since 2.0.0
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
