<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\ObjectManager\Test\Unit;

use Magento\Framework\ObjectManager\InterceptableValidator;
use Magento\Test\Di\Proxy;
use PHPUnit\Framework\TestCase;

require __DIR__ . '/_files/Proxy.php';

class InterceptableValidatorTest extends TestCase
{
    public function testValidate()
    {
        $interceptableValidator = new InterceptableValidator();
        $this->assertFalse(
            $interceptableValidator->validate(
                \Magento\Catalog\Controller\Adminhtml\Product\Initialization\Helper\Interceptor::class
            )
        );
        $this->assertFalse(
            $interceptableValidator->validate(
                Proxy::class
            )
        );
    }
}
