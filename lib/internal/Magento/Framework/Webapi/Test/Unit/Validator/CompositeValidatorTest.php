<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Framework\Webapi\Test\Unit\Validator;

use Magento\Framework\Exception\InvalidArgumentException;
use Magento\Framework\Webapi\Validator\CompositeServiceInputValidator;
use Magento\Framework\Webapi\Validator\ServiceInputValidatorInterface;
use PHPUnit\Framework\TestCase;

/**
 * Verify behavior of composite validator
 */
class CompositeValidatorTest extends TestCase
{
    public function testValidateEntityValue()
    {
        $object = new \stdClass();
        $validatorA = self::getMockBuilder(ServiceInputValidatorInterface::class)->getMock();
        $validatorA->expects(self::once())
            ->method('validateEntityValue')
            ->with($object, 'foo', 'abc');
        $validatorB = self::getMockBuilder(ServiceInputValidatorInterface::class)->getMock();
        $validatorB->expects(self::once())
            ->method('validateEntityValue')
            ->with($object, 'foo', 'abc');

        $composite = new CompositeServiceInputValidator([$validatorA, $validatorB]);
        $composite->validateEntityValue($object, 'foo', 'abc');
    }

    public function testValidateComplexArrayType()
    {
        $items = [['item1']];
        $validatorA = self::getMockBuilder(ServiceInputValidatorInterface::class)->getMock();
        $validatorA->expects(self::once())
            ->method('validateComplexArrayType')
            ->with('foo', $items);
        $validatorB = self::getMockBuilder(ServiceInputValidatorInterface::class)->getMock();
        $validatorB->expects(self::once())
            ->method('validateComplexArrayType')
            ->with('foo', $items);

        $composite = new CompositeServiceInputValidator([$validatorA, $validatorB]);
        $composite->validateComplexArrayType('foo', $items);
    }
}
