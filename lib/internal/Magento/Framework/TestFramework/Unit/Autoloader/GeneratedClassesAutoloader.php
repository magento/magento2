<?php
/**
 *  Copyright © Magento, Inc. All rights reserved.
 *  See COPYING.txt for license details.
 */

namespace Magento\Framework\TestFramework\Unit\Autoloader;

/**
 * Autoloader that initiates auto-generation of requested classes
 */
class GeneratedClassesAutoloader
{
    /**
     * @var \Magento\Framework\Code\Generator
     */
    private $generator;

    /**
     * FactoryGeneratorAutoloader constructor.
     * @param \Magento\Framework\Code\Generator $generator
     */
    public function __construct(\Magento\Framework\Code\Generator $generator)
    {
        $this->generator = $generator;
    }

    public function load($className)
    {
        $this->generator->generateClass($className);
    }
}
