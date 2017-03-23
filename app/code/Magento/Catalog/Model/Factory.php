<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

/**
 * Model factory
 */
namespace Magento\Catalog\Model;

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
     * Create model
     *
     * @param string $className
     * @param array $data
     * @return \Magento\Framework\Model\AbstractModel
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function create($className, array $data = [])
    {
        $model = $this->_objectManager->create($className, $data);

        if (!$model instanceof \Magento\Framework\Model\AbstractModel) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('%1 doesn\'t extends \Magento\Framework\Model\AbstractModel', $className)
            );
        }
        return $model;
    }
}
