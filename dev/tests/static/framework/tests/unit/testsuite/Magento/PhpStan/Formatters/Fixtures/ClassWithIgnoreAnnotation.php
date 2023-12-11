<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\PhpStan\Formatters\Fixtures;

/**
 * Class ClassWithIgnoreAnnotation
 *
 * phpcs:ignoreFile
 */
class ClassWithIgnoreAnnotation
{
    /**
     * Test method.
     * phpstan:ignore "Method level error"
     */
    public function getProductList()
    {
        // phpstan:ignore "Method Magento\PhpStan\Formatters\Fixtures\ClassWithIgnoreAnnotation::testMethod() invoked with 2 parameters, 1 required."
        $this->testMethod('test1', 'test2');

        // phpstan:ignore "Method * invoked with 2 parameters, 1 required."
        $this->testMethod('test1', 'test2');

        // phpstan:ignore
        $this->testMethod('test1', 'test2');

        $this->testMethod('test1', 'test2'); // phpstan:ignore
    }

    /**
     * @param string $arg1
     * @return string
     */
    private function testMethod(string $arg1)
    {
        return $arg1;
    }
}
