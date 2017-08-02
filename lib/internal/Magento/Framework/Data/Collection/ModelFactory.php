<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Data\Collection;

/**
 * Model object factory
 * @since 2.0.0
 */
class ModelFactory
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     * @since 2.0.0
     */
    protected $_objectManager;

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @since 2.0.0
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
     * @since 2.0.0
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
