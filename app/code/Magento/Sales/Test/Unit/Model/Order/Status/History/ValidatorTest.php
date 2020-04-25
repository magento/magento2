<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Model\Order\Status\History;

use Magento\Sales\Model\Order\Status\History;
use Magento\Sales\Model\Order\Status\History\Validator;
use PHPUnit\Framework\TestCase;

class ValidatorTest extends TestCase
{
    public function testValidate()
    {
        $history = $this->createPartialMock(History::class, ['hasData']);
        $history->expects($this->any())
            ->method('hasData')
            ->willReturn(true);
        $validator = new Validator();
        $this->assertEmpty($validator->validate($history));
    }

    public function testValidateNegative()
    {
        $history = $this->createPartialMock(History::class, ['hasData']);
        $history->expects($this->any())
            ->method('hasData')
            ->with('parent_id')
            ->willReturn(false);
        $validator = new Validator();
        $this->assertEquals(['"Order Id" is required. Enter and try again.'], $validator->validate($history));
    }
}
