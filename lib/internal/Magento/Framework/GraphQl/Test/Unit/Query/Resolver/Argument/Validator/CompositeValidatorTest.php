<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Framework\GraphQl\Test\Unit\Query\Resolver\Argument\Validator;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\Resolver\Argument\Validator\CompositeValidator;
use Magento\Framework\GraphQl\Query\Resolver\Argument\ValidatorInterface;
use PHPUnit\Framework\TestCase;

/**
 * Verify behavior of composite validator
 */
class CompositeValidatorTest extends TestCase
{
    public function testValidate()
    {
        $field = self::getMockBuilder(Field::class)
            ->disableOriginalConstructor()
            ->getMock();
        $args = ['a' => 123];
        $validatorA = self::getMockBuilder(ValidatorInterface::class)->getMock();
        $validatorA->expects(self::once())
            ->method('validate')
            ->with($field, $args);
        $validatorB = self::getMockBuilder(ValidatorInterface::class)->getMock();
        $validatorB->expects(self::once())
            ->method('validate')
            ->with($field, $args);

        $composite = new CompositeValidator([$validatorA, $validatorB]);
        $composite->validate($field, $args);
    }
}
