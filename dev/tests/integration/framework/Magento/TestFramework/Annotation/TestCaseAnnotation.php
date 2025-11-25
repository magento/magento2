<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\TestFramework\Annotation;

use PHPUnit\Framework\TestCase;
use PHPUnit\Util\Exception;
use PHPUnit\Metadata\Annotation\Parser\Registry;

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
        $methodName = $testCase->name();

        return [
            'method' => $methodName ? $registry->forMethod($className, $methodName)->symbolAnnotations() : null,
            'class'  => $registry->forClassName($className)->symbolAnnotations(),
        ];
    }
}
