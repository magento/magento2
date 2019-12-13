<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Framework\Config\Test\Unit\ValidationState;

use Magento\Framework\Config\ValidationState\Configurable;
use PHPUnit\Framework\TestCase;

/**
 * Tests for configurable validation state
 */
class ConfigurableTest extends TestCase
{
    public function testTrue()
    {
        $state = new Configurable(true);
        self::assertTrue($state->isValidationRequired());
    }

    public function testFalse()
    {
        $state = new Configurable(false);
        self::assertFalse($state->isValidationRequired());
    }
}
