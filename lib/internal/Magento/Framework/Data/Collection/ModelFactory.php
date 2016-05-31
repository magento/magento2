<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Data\Collection;

/**
 * Model object factory
 */
class ModelFactory
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->_objectManager = $objectManager;
    }

    /**
     * Create new model object
     *
     * @param string $model
     * @param array $data
     * @throws \InvalidArgumentException
     * @return \Magento\Framework\Model\AbstractModel
     */
    public function create($model, array $data = [])
    {
        $modelInstance = $this->_objectManager->create($model, $data);
        if (false == $modelInstance instanceof \Magento\Framework\Model\AbstractModel) {
            throw new \InvalidArgumentException($model . ' is not instance of \Magento\Framework\Model\AbstractModel');
        }
        return $modelInstance;
    }
}
