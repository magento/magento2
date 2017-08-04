<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Code\Generator;

use Magento\Framework\Code\Generator;

/**
 * Class \Magento\Framework\Code\Generator\Autoloader
 *
 */
class Autoloader
{
    /**
     * @var \Magento\Framework\Code\Generator
     */
    protected $_generator;

    /**
     * @param \Magento\Framework\Code\Generator $generator
     */
    public function __construct(
        \Magento\Framework\Code\Generator $generator
    ) {
        $this->_generator = $generator;
    }

    /**
     * Load specified class name and generate it if necessary
     *
     * @param string $className
     * @return bool True if class was loaded
     */
    public function load($className)
    {
        if (!class_exists($className)) {
            return Generator::GENERATION_ERROR != $this->_generator->generateClass($className);
        }
        return true;
    }
}
