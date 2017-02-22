<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Test\Unit\Model\Order\Status\History;

use \Magento\Sales\Model\Order\Status\History\Validator;

class ValidatorTest extends \PHPUnit_Framework_TestCase
{
    public function testValidate()
    {
        $history = $this->getMock('Magento\Sales\Model\Order\Status\History', ['hasData'], [], '', false);
        $history->expects($this->any())
            ->method('hasData')
            ->will($this->returnValue(true));
        $validator = new Validator();
        $this->assertEmpty($validator->validate($history));
    }

    public function testValidateNegative()
    {
        $history = $this->getMock('Magento\Sales\Model\Order\Status\History', ['hasData'], [], '', false);
        $history->expects($this->any())
            ->method('hasData')
            ->with('parent_id')
            ->will($this->returnValue(false));
        $validator = new Validator();
        $this->assertEquals(['Order Id is a required field'], $validator->validate($history));
    }
}
