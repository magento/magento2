<?php
/**
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
namespace Magento\TestFramework\Integrity\Library;

use Zend\Code\Reflection\ClassReflection;
use Zend\Code\Reflection\FileReflection;
use Zend\Code\Reflection\ParameterReflection;

/**
 */
class Injectable
{
    /**
     * @var \ReflectionException[]
     */
    protected $dependencies = array();

    /**
     * @param FileReflection $fileReflection
     * @return \ReflectionException[]
     * @throws \ReflectionException
     */
    public function getDependencies(FileReflection $fileReflection)
    {
        foreach ($fileReflection->getClasses() as $class) {
            /** @var ClassReflection $class */
            foreach ($class->getMethods() as $method) {
                /** @var \Zend\Code\Reflection\MethodReflection $method */
                if ($method->getDeclaringClass()->getName() != $class->getName()) {
                    continue;
                }

                foreach ($method->getParameters() as $parameter) {
                    try {
                        /** @var ParameterReflection $parameter */
                        $dependency = $parameter->getClass();
                        if ($dependency instanceof ClassReflection) {
                            $this->dependencies[] = $dependency->getName();
                        }
                    } catch (\ReflectionException $e) {
                        if (preg_match('#^Class ([A-Za-z0-9_\\\\]+) does not exist$#', $e->getMessage(), $result)) {
                            $this->dependencies[] = $result[1];
                        } else {
                            throw $e;
                        }
                    }
                }
            }
        }

        return $this->dependencies;
    }
}
