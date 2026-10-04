<?php
// phpcs:ignoreFile
/**
 * Copyright 2021 Adobe
 * All Rights Reserved.
 */

namespace Magento\TestFramework\Integrity\Library;

use Magento\Framework\DataObject;
use TestNamespace\Some\SomeTestClass;

/**
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
 */
class DummyInjectableClass
{
    public function testMethod(DataObject $dataObject, SomeTestClass $test)
    {
    }

    private function otherTest(\TestNamespace\Other\Test $test)
    {
    }
}
