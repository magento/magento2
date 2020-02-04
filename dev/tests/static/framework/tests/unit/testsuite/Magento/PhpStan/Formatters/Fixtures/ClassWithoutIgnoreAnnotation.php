<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\PhpStan\Formatters\Fixtures;

/**
 * Class ClassWithoutIgnoreAnnotation
 * phpcs:ignoreFile
 */
class ClassWithoutIgnoreAnnotation
{
    /**
     * Test method.
     */
    public function getProductList()
    {
        $this->testMethod('test1', 'test2');
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
