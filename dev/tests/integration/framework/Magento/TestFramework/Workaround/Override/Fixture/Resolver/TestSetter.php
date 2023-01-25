<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\TestFramework\Workaround\Override\Fixture\Resolver;

use Magento\Framework\Exception\LocalizedException;
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;
use PHPUnit\Framework\TestCase;

/**
 * Class set current test to fixture resolver instance
 *
 * @see \Magento\TestFramework\Workaround\Override\Fixture\Resolver
 */
class TestSetter
{
    /**
     * Handler for 'startTest' event
     *
     * @param TestCase $test
     */
    public function startTest(TestCase $test)
    {
        $resolver = Resolver::getInstance();
        if ($resolver->getCurrentTest() !== null) {
            throw new LocalizedException(__('Fixture resolver should not have test before test run'));
        }

        $resolver->setCurrentTest($test);
    }

    /**
     * Handler for 'endTest' event
     *
     * @param TestCase $test
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function endTest(TestCase $test)
    {
        $resolver = Resolver::getInstance();
        $resolver->setCurrentTest(null);
    }
}
