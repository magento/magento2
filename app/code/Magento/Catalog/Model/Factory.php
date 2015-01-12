<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

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
     * @throws \Magento\Framework\Model\Exception
     */
    public function create($className, array $data = [])
    {
        $model = $this->_objectManager->create($className, $data);

        if (!$model instanceof \Magento\Framework\Model\AbstractModel) {
            throw new \Magento\Framework\Model\Exception($className . ' doesn\'t extends \Magento\Framework\Model\AbstractModel');
        }
        return $model;
    }
}
