<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Code\Validator;

use Magento\Framework\Code\ValidatorInterface;

/**
 * Class \Magento\Framework\Code\Validator\ConstructorArgumentTypes
 *
 * @since 2.0.0
 */
class ConstructorArgumentTypes implements ValidatorInterface
{
    /**
     * @var \Magento\Framework\Code\Reader\ArgumentsReader
     * @since 2.0.0
     */
    protected $argumentsReader;

    /**
     * @var \Magento\Framework\Code\Reader\SourceArgumentsReader
     * @since 2.0.0
     */
    protected $sourceArgumentsReader;

    /**
     * @param \Magento\Framework\Code\Reader\ArgumentsReader $argumentsReader
     * @param \Magento\Framework\Code\Reader\SourceArgumentsReader $sourceArgumentsReader
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Framework\Code\Reader\ArgumentsReader $argumentsReader = null,
        \Magento\Framework\Code\Reader\SourceArgumentsReader $sourceArgumentsReader = null
    ) {
        $this->argumentsReader = $argumentsReader ?: new \Magento\Framework\Code\Reader\ArgumentsReader();
        $this->sourceArgumentsReader =
            $sourceArgumentsReader ?: new \Magento\Framework\Code\Reader\SourceArgumentsReader();
    }

    /**
     * Validate class constructor arguments
     *
     * @param string $className
     * @return bool
     * @throws \Magento\Framework\Exception\ValidatorException
     * @since 2.0.0
     */
    public function validate($className)
    {
        $class = new \ReflectionClass($className);
        $expectedArguments = $this->argumentsReader->getConstructorArguments($class);
        $actualArguments = array_filter($this->sourceArgumentsReader->getConstructorArgumentTypes($class));
        $expectedArguments = array_map(function ($element) {
            return $element['type'];
        }, $expectedArguments);

        foreach ($actualArguments as $argument) {
            if (!in_array($argument, $expectedArguments)) {
                throw new \Magento\Framework\Exception\ValidatorException(
                    new \Magento\Framework\Phrase(
                        'Invalid constructor argument(s) in %1',
                        [$className]
                    )
                );
            }
        }
        return true;
    }
}
