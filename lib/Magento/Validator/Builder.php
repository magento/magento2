<?php
/**
 * Magento Validator Builder
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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Validator;

class Builder
{
    /**
     * @var array
     */
    protected $_constraints;

    /**
     * @var \Magento\Validator\ConstraintFactory
     */
    protected $_constraintFactory;

    /**
     * @var \Magento\ValidatorFactory
     */
    protected $_validatorFactory;

    /**
     * @var \Magento\Validator\UniversalFactory
     */
    protected $_oneValidatorFactory;

    /**
     * @param \Magento\Validator\ConstraintFactory $constraintFactory
     * @param \Magento\ValidatorFactory $validatorFactory
     * @param \Magento\Validator\UniversalFactory $oneValidatorFactory
     * @param array $constraints
     */
    public function __construct(
        \Magento\Validator\ConstraintFactory $constraintFactory,
        \Magento\ValidatorFactory $validatorFactory,
        \Magento\Validator\UniversalFactory $oneValidatorFactory,
        array $constraints
    ) {
        foreach ($constraints as $constraint) {
            if (isset($constraint['options']) && is_array($constraint['options'])) {
                $this->_checkConfigurationArguments($constraint['options'], true);
                $this->_checkConfigurationCallback($constraint['options'], true);
            }
        }
        $this->_constraints = $constraints;
        $this->_constraintFactory = $constraintFactory;
        $this->_validatorFactory = $validatorFactory;
        $this->_oneValidatorFactory = $oneValidatorFactory;
    }

    /**
     * Check configuration arguments
     *
     * @param array $configuration
     * @param bool $argumentsIsArray
     * @throws \InvalidArgumentException
     */
    protected function _checkConfigurationArguments(array $configuration, $argumentsIsArray)
    {
        // https://jira.corp.x.com/browse/MAGETWO-10439
        $allowedKeys = array('arguments', 'callback', 'method', 'methods', 'breakChainOnFailure');
        if (!array_intersect($allowedKeys, array_keys($configuration))) {
            throw new \InvalidArgumentException('Configuration has incorrect format');
        }
        // Check method arguments
        if ($argumentsIsArray) {
            if (array_key_exists('methods', $configuration)) {
                foreach ($configuration['methods'] as $method) {
                    $this->_checkMethodArguments($method);
                }
            }
        } elseif (array_key_exists('method', $configuration)) {
            $this->_checkMethodArguments($configuration);
        }

        // Check constructor arguments
        if (array_key_exists('arguments', $configuration) && !is_array($configuration['arguments'])) {
            throw new \InvalidArgumentException('Arguments must be an array');
        }
    }

    /**
     * Check configuration method arguments
     *
     * @param array $configuration
     * @throws \InvalidArgumentException
     */
    protected function _checkMethodArguments(array $configuration)
    {
        if (!is_string($configuration['method'])) {
            throw new \InvalidArgumentException('Method has to be passed as string');
        }
        if (array_key_exists('arguments', $configuration) && !is_array($configuration['arguments'])) {
            throw new \InvalidArgumentException('Method arguments must be an array');
        }
    }

    /**
     * Check configuration callbacks
     *
     * @param array $configuration
     * @param bool $callbackIsArray
     * @throws \InvalidArgumentException
     */
    protected function _checkConfigurationCallback(array $configuration, $callbackIsArray)
    {
        if (array_key_exists('callback', $configuration)) {
            if ($callbackIsArray) {
                $callbacks = $configuration['callback'];
            } else {
                $callbacks = array($configuration['callback']);
            }
            foreach ($callbacks as $callback) {
                if (!($callback instanceof \Magento\Validator\Constraint\Option\Callback)) {
                    throw new \InvalidArgumentException(
                        'Callback must be instance of \Magento\Validator\Constraint\Option\Callback'
                    );
                }
            }
        }
    }

    /**
     * Create validator instance and configure it
     *
     * @return \Magento\Validator
     */
    public function createValidator()
    {
        return $this->_createValidatorInstance();
    }

    /**
     * Get validator instance
     *
     * @return \Magento\Validator
     */
    protected function _createValidatorInstance()
    {
        $validator = $this->_validatorFactory->create();
        foreach ($this->_constraints as $constraintData) {
            // https://jira.corp.x.com/browse/MAGETWO-10439
            $breakChainOnFailure = !empty($constraintData['options']['breakChainOnFailure']);
            $validator->addValidator($this->_createConstraint($constraintData), $breakChainOnFailure);
        }
        return $validator;
    }

    /**
     * Add constraint configuration
     *
     * @param string $alias
     * @param array $configuration
     * @throws \InvalidArgumentException
     * @return \Magento\Validator\Builder
     */
    public function addConfiguration($alias, array $configuration)
    {
        $this->_checkConfigurationArguments($configuration, false);
        $this->_checkConfigurationCallback($configuration, false);
        foreach ($this->_constraints as &$constraint) {
            if ($constraint['alias'] != $alias) {
                continue;
            }
            if (!array_key_exists('options', $constraint) || !is_array($constraint['options'])) {
                $constraint['options'] = array();
            }
            if (!array_key_exists('method', $configuration)) {
                if (array_key_exists('arguments', $configuration)) {
                    $constraint['options']['arguments'] = $configuration['arguments'];
                } elseif (array_key_exists('callback', $configuration)) {
                    $constraint = $this->_addConstraintCallback($constraint, $configuration['callback']);
                }
            } else {
                $constraint = $this->_addConstraintMethod($constraint, $configuration);
            }
        }

        return $this;
    }

    /**
     * Add callback to constraint configuration
     *
     * @param array $constraint
     * @param \Magento\Validator\Constraint\Option\Callback $callback
     * @return array
     */
    protected function _addConstraintCallback(array $constraint, \Magento\Validator\Constraint\Option\Callback $callback)
    {
        if (!array_key_exists('callback', $constraint['options'])) {
            $constraint['options']['callback'] = array();
        }
        $constraint['options']['callback'][] = $callback;
        return $constraint;
    }

    /**
     * Add method to constraint configuration
     *
     * @param array $constraint
     * @param array $configuration
     * @return array
     */
    protected function _addConstraintMethod(array $constraint, array $configuration)
    {
        if (!array_key_exists('methods', $constraint['options'])) {
            $constraint['options']['methods'] = array();
        }
        $constraint['options']['methods'][] = $configuration;
        return $constraint;
    }

    /**
     * Add constraints configuration
     *
     * @param array $configurations
     * @return \Magento\Validator\Builder
     */
    public function addConfigurations(array $configurations)
    {
        foreach ($configurations as $alias => $concreteConfigs) {
            foreach ($concreteConfigs as $configuration) {
                $this->addConfiguration($alias, $configuration);
            }
        }
        return $this;
    }

    /**
     * Create constraint from data
     *
     * @param array $data
     * @return \Magento\Validator\Constraint
     */
    protected function _createConstraint(array $data)
    {
        // Create validator instance
        $validator = $this->_createConstraintValidator($data);
        if (isset($data['options']) && is_array($data['options'])) {
            $this->_configureConstraintValidator($validator, $data['options']);
        }

        if (\Magento\Validator\Config::CONSTRAINT_TYPE_PROPERTY == $data['type']) {
            $result = new \Magento\Validator\Constraint\Property($validator, $data['property'], $data['alias']);
        } else {
            $result = $this->_constraintFactory->create(array('validator' => $validator, 'alias' => $data['alias']));
        }

        return $result;
    }

    /**
     * Create constraint validator instance
     *
     * @param array $data
     * @throws \InvalidArgumentException
     * @return \Magento\Validator\ValidatorInterface
     */
    protected function _createConstraintValidator(array $data)
    {
        $validator = $this->_oneValidatorFactory->create(
            $data['class'],
            array('options' => isset($data['options']['arguments'])
                ? $this->_applyArgumentsCallback($data['options']['arguments'])
                : array()
            )
        );

        // Check validator type
        if (!$validator instanceof \Magento\Validator\ValidatorInterface) {
            throw new \InvalidArgumentException(sprintf(
                'Constraint class "%s" must implement \Magento\Validator\ValidatorInterface', $data['class']
            ));
        }

        return $validator;
    }

    /**
     * Configure validator
     *
     * @param \Magento\Validator\ValidatorInterface $validator
     * @param array $options
     */
    protected function _configureConstraintValidator(\Magento\Validator\ValidatorInterface $validator, array $options)
    {
        // Call all validator methods according to configuration
        if (isset($options['methods'])) {
            foreach ($options['methods'] as $methodData) {
                $methodName = $methodData['method'];
                if (method_exists($validator, $methodName)) {
                    if (array_key_exists('arguments', $methodData)) {
                        $arguments = $this->_applyArgumentsCallback($methodData['arguments']);
                        call_user_func_array(array($validator, $methodName), $arguments);
                    } else {
                        call_user_func(array($validator, $methodName));
                    }
                }
            }
        }

        // Call validator configurators if any
        if (isset($options['callback'])) {
            /** @var $callback \Magento\Validator\Constraint\Option\Callback */
            foreach ($options['callback'] as $callback) {
                $callback->setArguments($validator);
                $callback->getValue();
            }
        }
    }

    /**
     * Apply all argument callback
     *
     * @param \Magento\Validator\Constraint\OptionInterface[] $arguments
     * @return array
     */
    protected function _applyArgumentsCallback(array $arguments)
    {
        foreach ($arguments as &$argument) {
            if ($argument instanceof \Magento\Validator\Constraint\OptionInterface) {
                $argument = $argument->getValue();
            }
        }
        return $arguments;
    }
}
