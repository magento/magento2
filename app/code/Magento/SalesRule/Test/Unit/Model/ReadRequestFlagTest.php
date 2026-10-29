<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\SalesRule\Test\Unit\Model;

use Magento\SalesRule\Model\ReadRequestFlag;
use PHPUnit\Framework\TestCase;

class ReadRequestFlagTest extends TestCase
{
    /**
     * @var ReadRequestFlag
     */
    private $state;

    protected function setUp(): void
    {
        $this->state = new ReadRequestFlag();
    }

    public function testDefaultStateIsFalse()
    {
        $this->assertFalse($this->state->isReadRequest());
    }

    public function testIsReadRequestTrue()
    {
        $this->state->setIsReadRequest(true);
        $this->assertTrue($this->state->isReadRequest());
    }

    public function testIsReadRequestFalse()
    {
        $this->state->setIsReadRequest(true);
        $this->state->setIsReadRequest(false);
        $this->assertFalse($this->state->isReadRequest());
    }

    public function testResetAndMultipleToggle()
    {
        $this->assertFalse($this->state->isReadRequest());

        $this->state->setIsReadRequest(true);
        $this->assertTrue($this->state->isReadRequest());

        $this->state->reset();
        $this->assertFalse($this->state->isReadRequest());

        $this->state->setIsReadRequest(true);
        $this->assertTrue($this->state->isReadRequest());
    }
}
