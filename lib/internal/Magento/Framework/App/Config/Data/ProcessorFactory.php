<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\App\Config\Data;

/**
 * @api
 */
class ProcessorFactory
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @var ProcessorInterface[]
     */
    protected $_pool;

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->_objectManager = $objectManager;
    }

    /**
     * Get concrete Processor Interface instance
     *
     * @param string $processorModel Classname of the instance to get
     * @return ProcessorInterface
     * @throws \InvalidArgumentException In case the given classname is not an instance of ProcessorInterface
     */
    public function get($processorModel)
    {
        if (!isset($this->_pool[$processorModel])) {
            $instance = $this->_objectManager->create($processorModel);
            if (!$instance instanceof ProcessorInterface) {
                throw new \InvalidArgumentException(
                    $processorModel . ' is not instance of \Magento\Framework\App\Config\Data\ProcessorInterface'
                );
            }
            $this->_pool[$processorModel] = $instance;
        }
        return $this->_pool[$processorModel];
    }
}
