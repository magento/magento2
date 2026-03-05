<?php
/**
 * Copyright 2020 Adobe
 * All rights reserved.
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
    public const SKIPPABLE_SUFFIX = 'Skippable';

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
        $docComment = $class->getDocComment();
        $longDescription = (is_array($docComment) || is_string($docComment))
            ? str_replace(['/**', '*/', '*'], '', $docComment)
            : '';

        $wrapperCode = $this->classGenerator->setNamespaceName($class->getName())
            ->setClassDocBlock(['longDescription' => $longDescription])
            ->setExtendedClass($class->getName())
            ->setName(self::SKIPPABLE_SUFFIX)
            ->setImplementedInterfaces([SkippableInterface::class])
            ->addTrait('\\' . SkippableTrait::class)
            ->generate();
        // phpcs:disable
        eval($wrapperCode);
        // phpcs:enable

        return $class->getName() . '\\' . self::SKIPPABLE_SUFFIX;
    }
}
