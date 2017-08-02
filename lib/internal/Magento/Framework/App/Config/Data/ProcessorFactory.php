<?php
/**
 * Config data Factory
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Config\Data;

/**
 * Class \Magento\Framework\App\Config\Data\ProcessorFactory
 *
 * @since 2.0.0
 */
class ProcessorFactory
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     * @since 2.0.0
     */
    protected $_objectManager;

    /**
     * @var ProcessorInterface[]
     * @since 2.0.0
     */
    protected $_pool;

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @since 2.0.0
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
     * @api
     * @since 2.0.0
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
