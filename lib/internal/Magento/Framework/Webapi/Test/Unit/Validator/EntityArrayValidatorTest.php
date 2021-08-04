<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Framework\Webapi\Test\Unit\Validator;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Webapi\Validator\EntityArrayValidator;
use PHPUnit\Framework\TestCase;

/**
 * Verifies behavior of the entity array validator
 */
class EntityArrayValidatorTest extends TestCase
{
    /**
     * @doesNotPerformAssertions
     */
    public function testAllowsDataWhenBelowLimit()
    {
        $validator = new EntityArrayValidator(3);
        $validator->validateComplexArrayType("foo", [[],[],[]]);
    }

    public function testFailsDataWhenAboveLimit()
    {
        $this->expectException(LocalizedException::class);
        $this->expectErrorMessage('Maximum items of type "foo" is 3');
        $validator = new EntityArrayValidator(3);
        $validator->validateComplexArrayType("foo", [[],[],[],[]]);
    }
}
