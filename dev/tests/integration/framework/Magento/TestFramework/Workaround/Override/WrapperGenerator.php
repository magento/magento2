<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\TestFramework\Workaround\Override;

use Magento\Framework\Code\Generator\ClassGenerator;
use Magento\TestFramework\SkippableInterface;
use Magento\TestFramework\SkippableTrait;

/**
 * Integration tests wrap generator
 */
class WrapperGenerator
{
    const SKIPPABLE_SUFFIX = 'Skippable';

    /**
     * @var ClassGenerator
     */
    private $classGenerator;

    /**
     * WrapperGenerator constructor.
     */
    public function __construct()
    {
        $this->classGenerator = new ClassGenerator();
    }

    /**
     * Generates test wrapper class and returns wrapper name.
     *
     * @param \ReflectionClass $class
     * @return string
     */
    public function generateTestWrapper(\ReflectionClass $class): string
    {
        $wrapperCode = $this->classGenerator->setNamespaceName($class->getName())
            ->setClassDocBlock(['longDescription' => str_replace(['/**', '*/', '*'], '', $class->getDocComment())])
            ->setExtendedClass($class->getName())
            ->setName(self::SKIPPABLE_SUFFIX)
            ->setImplementedInterfaces([SkippableInterface::class])
            ->addTrait('\\' . SkippableTrait::class)
            ->generate();
        // phpcs:ignore Squiz.PHP.Eval
        eval($wrapperCode);

        return $class->getName() . '\\' . self::SKIPPABLE_SUFFIX;
    }
}
