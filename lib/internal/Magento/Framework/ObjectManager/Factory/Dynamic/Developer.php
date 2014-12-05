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
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Framework\ObjectManager\Factory\Dynamic;

class Developer extends \Magento\Framework\ObjectManager\Factory\AbstractFactory
{
    /**
     * Object creation stack
     *
     * @var array
     */
    protected $creationStack = array();

    /**
     * Resolve constructor arguments
     *
     * @param string $requestedType
     * @param array $parameters
     * @param array $arguments
     *
     * @return array
     *
     * @throws \UnexpectedValueException
     * @throws \BadMethodCallException
     */
    protected function _resolveArguments($requestedType, array $parameters, array $arguments = array())
    {
        $resolvedArguments = array();
        $arguments = count($arguments)
            ? array_replace($this->config->getArguments($requestedType), $arguments)
            : $this->config->getArguments($requestedType);
        foreach ($parameters as $parameter) {
            list($paramName, $paramType, $paramRequired, $paramDefault) = $parameter;
            $argument = null;
            if (!empty($arguments) && (isset($arguments[$paramName]) || array_key_exists($paramName, $arguments))) {
                $argument = $arguments[$paramName];
            } else if ($paramRequired) {
                if ($paramType) {
                    $argument = array('instance' => $paramType);
                } else {
                    $this->creationStack = array();
                    throw new \BadMethodCallException(
                        'Missing required argument $' . $paramName . ' of ' . $requestedType . '.'
                    );
                }
            } else {
                $argument = $paramDefault;
            }

            $this->resolveArgument($argument, $paramType, $paramDefault, $paramName, $requestedType);

            $resolvedArguments[] = $argument;
        }
        return $resolvedArguments;
    }

    /**
     * Create instance with call time arguments
     *
     * @param string $requestedType
     * @param array $arguments
     * @return object
     * @throws \Exception
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function create($requestedType, array $arguments = array())
    {
        $type = $this->config->getInstanceType($requestedType);
        $parameters = $this->definitions->getParameters($type);
        if ($parameters == null) {
            return new $type();
        }
        if (isset($this->creationStack[$requestedType])) {
            $lastFound = end($this->creationStack);
            $this->creationStack = array();
            throw new \LogicException("Circular dependency: {$requestedType} depends on {$lastFound} and vice versa.");
        }
        $this->creationStack[$requestedType] = $requestedType;
        try {
            $args = $this->_resolveArguments($requestedType, $parameters, $arguments);
            unset($this->creationStack[$requestedType]);
        } catch (\Exception $e) {
            unset($this->creationStack[$requestedType]);
            throw $e;
        }

        return $this->createObject($type, $args);
    }
}
