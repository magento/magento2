<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\ObjectManager\Relations;

/**
 * Class \Magento\Framework\ObjectManager\Relations\Runtime
 *
 * @since 2.0.0
 */
class Runtime implements \Magento\Framework\ObjectManager\RelationsInterface
{
    /**
     * @var \Magento\Framework\Code\Reader\ClassReaderInterface
     * @since 2.0.0
     */
    protected $_classReader;

    /**
     * Default behavior
     *
     * @var array
     * @since 2.0.0
     */
    protected $_default = [];

    /**
     * @param \Magento\Framework\Code\Reader\ClassReaderInterface $classReader
     * @since 2.0.0
     */
    public function __construct(\Magento\Framework\Code\Reader\ClassReaderInterface $classReader = null)
    {
        $this->_classReader = $classReader ?: new \Magento\Framework\Code\Reader\ClassReader();
    }

    /**
     * Check whether requested type is available for read
     *
     * @param string $type
     * @return bool
     * @since 2.0.0
     */
    public function has($type)
    {
        return class_exists($type) || interface_exists($type);
    }

    /**
     * Retrieve list of parents
     *
     * @param string $type
     * @return array
     * @since 2.0.0
     */
    public function getParents($type)
    {
        if (!class_exists($type)) {
            return $this->_default;
        }
        return $this->_classReader->getParents($type) ?: $this->_default;
    }
}
