<?php
/**
 *
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Tools\Di\Code\Reader;

use \Magento\Tools\Di\Compiler\ConstructorArgument;

class ClassReaderDecorator implements \Magento\Framework\Code\Reader\ClassReaderInterface
{
    /**
     * @var \Magento\Framework\Code\Reader\ClassReader
     */
    private $classReader;

    /**
     * @param \Magento\Framework\Code\Reader\ClassReader $classReader
     */
    public function __construct(\Magento\Framework\Code\Reader\ClassReader $classReader)
    {
        $this->classReader = $classReader;
    }

    /**
     * Read class constructor signature
     *
     * @param string $className
     * @return ConstructorArgument[]|null
     * @throws \ReflectionException
     */
    public function getConstructor($className)
    {
        $unmappedArguments = $this->classReader->getConstructor($className);
        if (is_null($unmappedArguments)) {
            return $unmappedArguments;
        }

        $arguments = [];
        foreach ($unmappedArguments as $argument) {
            $arguments[] = new ConstructorArgument($argument);
        }

        return $arguments;
    }

    /**
     * Retrieve parent relation information for type in a following format
     * array(
     *     'Parent_Class_Name',
     *     'Interface_1',
     *     'Interface_2',
     *     ...
     * )
     *
     * @param string $className
     * @return string[]
     */
    public function getParents($className)
    {
        return $this->classReader->getParents($className);
    }
}
