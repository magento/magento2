<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\View\Test\Unit\Layout\Argument\Interpreter;

use Magento\Framework\View\Layout\Argument\Interpreter\Passthrough;
use PHPUnit\Framework\TestCase;

class PassthroughTest extends TestCase
{
    /**
     * @var Passthrough
     */
    protected $_model;

    protected function setUp(): void
    {
        $this->_model = new Passthrough();
    }

    public function testEvaluate()
    {
        $input = ['data' => 'some/value'];
        $expected = ['data' => 'some/value'];

        $actual = $this->_model->evaluate($input);
        $this->assertSame($expected, $actual);
    }
}
