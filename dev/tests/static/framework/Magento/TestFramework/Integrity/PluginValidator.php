<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\TestFramework\Integrity;

use Magento\Framework\Exception\ValidatorException;
use Magento\Framework\Interception\Code\InterfaceValidator;
use Magento\Framework\Phrase;

class PluginValidator
{
    /** @var InterfaceValidator */
    private $frameworkValidator;

    /**
     * @param InterfaceValidator $frameworkValidator
     */
    public function __construct(InterfaceValidator $frameworkValidator)
    {
        $this->frameworkValidator = $frameworkValidator;
    }

    /**
     * Validate plugin and intercepted class
     *
     * @param string $pluginClass
     * @param string $interceptedType
     * @throws ValidatorException
     */
    public function validate($pluginClass, $interceptedType)
    {
        $this->frameworkValidator->validate($pluginClass, $interceptedType);
        $this->validateClassNameMatchesCase($pluginClass);
        $this->validateClassNameMatchesCase($interceptedType);
    }

    /**
     * Verify that the class name has same capitalization as the class declaration
     *
     * @param string $className
     * @throws ValidatorException
     */
    private function validateClassNameMatchesCase($className)
    {
        $declarationName = (new \ReflectionClass($className))->getName();;
        if (ltrim($className, '\\') != ltrim($declarationName)) {
            throw new ValidatorException(
                new Phrase(
                    "Capitalization of class name '%1' does not match expected '%2'",
                    [$className, $declarationName]
                )
            );
        }
    }
}
