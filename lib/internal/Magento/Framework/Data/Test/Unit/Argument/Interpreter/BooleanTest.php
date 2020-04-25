<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Data\Test\Unit\Argument\Interpreter;

use Magento\Framework\Data\Argument\Interpreter\Boolean;
use Magento\Framework\Stdlib\BooleanUtils;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class BooleanTest extends TestCase
{
    /**
     * @var Boolean
     */
    protected $_model;

    /**
     * @var MockObject
     */
    protected $_booleanUtils;

    protected function setUp(): void
    {
        $this->_booleanUtils = $this->createMock(BooleanUtils::class);
        $this->_model = new Boolean($this->_booleanUtils);
    }

    public function testEvaluateException()
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('Boolean value is missing');
        $this->_model->evaluate([]);
    }

    public function testEvaluate()
    {
        $input = new \stdClass();
        $expected = new \stdClass();
        $this->_booleanUtils->expects(
            $this->once()
        )->method(
            'toBoolean'
        )->with(
            $this->identicalTo($input)
        )->willReturn(
            $expected
        );
        $actual = $this->_model->evaluate(['value' => $input]);
        $this->assertSame($expected, $actual);
    }
}
