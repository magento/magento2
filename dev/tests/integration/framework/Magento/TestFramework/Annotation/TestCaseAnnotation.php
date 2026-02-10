<?php
/**
 * Copyright 2021 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\TestFramework\Annotation;

use PHPUnit\Framework\TestCase;
use PHPUnit\Metadata\Annotation\Parser\Registry;
use PHPUnit\Util\Exception;
use ReflectionClass;
use ReflectionException;

/**
 * Returns annotations for given testcase.
 */
class TestCaseAnnotation
{
    /**
     * @var TestCaseAnnotation
     */
    private static $instance;

    /**
     * Get instance of test case annotation access service.
     *
     * @return TestCaseAnnotation
     */
    public static function getInstance(): TestCaseAnnotation
    {
        return self::$instance ?? self::$instance = new TestCaseAnnotation();
    }

    /**
     * Get annotations for the given test case.
     *
     * @param TestCase $testCase
     *
     * @return array
     */
    public function getAnnotations(TestCase $testCase): array
    {
        $registry = Registry::getInstance();
        $className = get_class($testCase);

        // Use reflection to safely check if name property is initialized
        // The 'name' property is from PHPUnit\Framework\TestCase, not the child class
        try {
            $reflection = new ReflectionClass(TestCase::class);
            $nameProperty = $reflection->getProperty('name');
            $methodName = $nameProperty->isInitialized($testCase) ? $testCase->name() : null;
        } catch (ReflectionException $e) {
            // If property doesn't exist or can't be accessed, fallback to null
            $methodName = null;
        }

        return [
            'method' => $methodName ? $registry->forMethod($className, $methodName)->symbolAnnotations() : [],
            'class'  => $registry->forClassName($className)->symbolAnnotations(),
        ];
    }
}
