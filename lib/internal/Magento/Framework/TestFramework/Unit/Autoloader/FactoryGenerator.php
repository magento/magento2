<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\TestFramework\Unit\Autoloader;

use Magento\Framework\Code\Generator\ClassGenerator;

/**
 * Generates a simple factory class with create() method
 */
class FactoryGenerator implements GeneratorInterface
{
    /**
     * Generates a factory class if it follows "<SourceClass>Factory" convention
     *
     * @param string $className
     * @return bool|string
     */
    public function generate($className)
    {
        if (!$this->isFactory($className)) {
            return false;
        }
        $methods = [[
            'name' => 'create',
            'parameters' => [['name' => 'data', 'type' => 'array', 'defaultValue' => []]],
            'body' => '',
        ]];
        $classGenerator = new ClassGenerator();
        $classGenerator->setName($className)
            ->addMethods($methods);
        return $classGenerator->generate();
    }

    /**
     * Check if the class name is a factory by convention "<SourceClass>Factory"
     *
     * @param string $className
     * @return bool
     */
    private function isFactory($className)
    {
        $sourceName = rtrim(substr($className, 0, -strlen('Factory')), '\\');
        return $sourceName . 'Factory' == $className;
    }
}
