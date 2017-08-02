<?php
/**
 * Class constructor validator. Validates argument types duplication
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Code\Validator;

use Magento\Framework\Code\ValidatorInterface;

/**
 * Class \Magento\Framework\Code\Validator\TypeDuplication
 *
 */
class TypeDuplication implements ValidatorInterface
{
    /**
     * Name of the suppress warnings annotation.
     */
    const SUPPRESS_ANNOTATION = 'SuppressWarnings';

    const TYPE_DUPLICATIONS = 'Magento.TypeDuplication';

    /**
     * @var \Magento\Framework\Code\Reader\ArgumentsReader
     */
    protected $_argumentsReader;

    /**
     * @var \Magento\Framework\Code\Reader\ScalarTypesProvider
     * @since 2.2.0
     */
    private $scalarTypesProvider;

    /**
     * @param \Magento\Framework\Code\Reader\ArgumentsReader|null $argumentsReader
     * @param \Magento\Framework\Code\Reader\ScalarTypesProvider|null $scalarTypesProvider
     */
    public function __construct(
        \Magento\Framework\Code\Reader\ArgumentsReader $argumentsReader = null,
        \Magento\Framework\Code\Reader\ScalarTypesProvider $scalarTypesProvider = null
    ) {
        $this->_argumentsReader = $argumentsReader ?: new \Magento\Framework\Code\Reader\ArgumentsReader();
        $this->scalarTypesProvider = $scalarTypesProvider ?: new \Magento\Framework\Code\Reader\ScalarTypesProvider();
    }

    /**
     * Validate class
     *
     * @param string $className
     * @return bool
     * @throws \Magento\Framework\Exception\ValidatorException
     */
    public function validate($className)
    {
        $class = new \ReflectionClass($className);
        $classArguments = $this->_argumentsReader->getConstructorArguments($class, true);

        $arguments = $this->_getObjectArguments($classArguments);

        $typeList = [];
        $errors = [];
        foreach ($arguments as $argument) {
            $name = $argument['name'];
            $type = $argument['type'];
            if (in_array($type, $typeList)) {
                $errors[] = 'Multiple type injection [' . $type . ']';
            } elseif (isset($typeList[$name])) {
                $errors[] = 'Variable name duplication. [$' . $name . ']';
            }
            $typeList[$name] = $type;
        }

        if (!empty($errors)) {
            if (false == $this->_ignoreWarning($class)) {
                $classPath = str_replace('\\', '/', $class->getFileName());
                throw new \Magento\Framework\Exception\ValidatorException(
                    new \Magento\Framework\Phrase(
                        'Argument type duplication in class %1 in %2%3%4',
                        [
                            $class->getName(),
                            $classPath,
                            PHP_EOL,
                            implode(PHP_EOL, $errors)
                        ]
                    )
                );
            }
        }
        return true;
    }

    /**
     * Get arguments with object types
     *
     * @param array $arguments
     * @return array
     */
    protected function _getObjectArguments(array $arguments)
    {
        $output = [];
        foreach ($arguments as $argument) {
            $type = $argument['type'];
            if (!$type || in_array($type, $this->scalarTypesProvider->getTypes())) {
                continue;
            }
            $reflection = new \ReflectionClass($type);
            if (false == $reflection->isInterface()) {
                $output[] = $argument;
            }
        }
        return $output;
    }

    /**
     * Check whether warning must be skipped
     *
     * @param \ReflectionClass $class
     * @return bool
     */
    protected function _ignoreWarning(\ReflectionClass $class)
    {
        $annotations = $this->_argumentsReader->getAnnotations($class);
        if (isset($annotations[self::SUPPRESS_ANNOTATION])) {
            return $annotations[self::SUPPRESS_ANNOTATION] == self::TYPE_DUPLICATIONS;
        }
        return false;
    }
}
