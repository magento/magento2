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
 * @category    Magento
 * @package     Magento_Di
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

use Zend\Di\Exception,
    Zend\Code\Reflection;

class Magento_Di_Definition_CompilerDefinition_Zend extends Zend\Di\Definition\CompilerDefinition
    implements Magento_Di_Definition_CompilerDefinition
{
    /**
     * Process class method parameters
     *
     * @param array $def
     * @param Zend\Code\Reflection\ClassReflection $rClass
     * @param Zend\Code\Reflection\MethodReflection $rMethod
     */
    protected function processParams(&$def, Reflection\ClassReflection $rClass, Reflection\MethodReflection $rMethod)
    {
        if (count($rMethod->getParameters()) === 0) {
            return;
        }

        parent::processParams($def, $rClass, $rMethod);

        $methodName = $rMethod->getName();

        /** @var $p \ReflectionParameter */
        foreach ($rMethod->getParameters() as $p) {
            $fqName = $rClass->getName() . '::' . $rMethod->getName() . ':' . $p->getPosition();

            $def['parameters'][$methodName][$fqName][] = ($p->isOptional() && $p->isDefaultValueAvailable())
                ? $p->getDefaultValue()
                : null;
        }
    }

    /**
     * Get definition as array
     *
     * @return array
     */
    public function toArray()
    {
        return $this->toArrayDefinition()->toArray();
    }

    /**
     * Convert to array definition
     *
     * @return Magento_Di_Definition_ArrayDefinition
     */
    public function toArrayDefinition()
    {
        return new Magento_Di_Definition_ArrayDefinition_Zend(
            $this->classes
        );
    }
}
