<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\DesignEditor\Model\Url;

class Factory
{
    /**
     * Default url model class name
     */
    const CLASS_NAME = 'Magento\Framework\UrlInterface';

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
     * Replace name of url model
     *
     * @param string $className
     * @return $this
     */
    public function replaceClassName($className)
    {
        $this->_objectManager->configure(['preferences' => [self::CLASS_NAME => $className]]);

        return $this;
    }

    /**
     * Create url model new instance
     *
     * @param array $arguments
     * @return \Magento\Framework\UrlInterface
     */
    public function create(array $arguments = [])
    {
        return $this->_objectManager->create(self::CLASS_NAME, $arguments);
    }
}
