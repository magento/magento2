<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Factory creating \Magento\Framework\Validator\Builder and \Magento\Framework\Validator\Validator
 *
 * @TODO Eliminate this factory in favor of strictly typified, not involving object manager with arbitrary class name
 */
namespace Magento\Framework\Validator;

/**
 * Class \Magento\Framework\Validator\UniversalFactory
 *
 * @since 2.0.0
 */
class UniversalFactory
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
     * @param string $className
     * @param array $arguments
     * @return \Magento\Framework\Validator\Builder
     * @since 2.0.0
     */
    public function create($className, array $arguments = [])
    {
        return $this->_objectManager->create($className, $arguments);
    }
}
