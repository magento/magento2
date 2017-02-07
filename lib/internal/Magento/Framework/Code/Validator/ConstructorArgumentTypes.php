<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Code\Validator;

use Magento\Framework\Code\ValidatorInterface;

class ConstructorArgumentTypes implements ValidatorInterface
{
    /**
     * @var \Magento\Framework\Code\Reader\ArgumentsReader
     */
    protected $argumentsReader;

    /**
     * @var \Magento\Framework\Code\Reader\SourceArgumentsReader
     */
    protected $sourceArgumentsReader;

    /**
     * @param \Magento\Framework\Code\Reader\ArgumentsReader $argumentsReader
     * @param \Magento\Framework\Code\Reader\SourceArgumentsReader $sourceArgumentsReader
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
     */
    public function validate($className)
    {
        $class = new \ReflectionClass($className);
        $expectedArguments = $this->argumentsReader->getConstructorArguments($class);
        $actualArguments = $this->sourceArgumentsReader->getConstructorArgumentTypes($class);
        $expectedArguments = array_map(function ($element) {
            return $element['type'];
        }, $expectedArguments);
        $result = array_diff($expectedArguments, $actualArguments);
        if (!empty($result)) {
            throw new \Magento\Framework\Exception\ValidatorException(
                new \Magento\Framework\Phrase(
                    'Invalid constructor argument(s) in %1',
                    [$className]
                )
            );
        }
        return true;
    }
}
