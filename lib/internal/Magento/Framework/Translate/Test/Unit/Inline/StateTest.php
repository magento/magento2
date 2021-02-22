<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Translate\Test\Unit\Inline;

use \Magento\Framework\Translate\Inline\State;

class StateTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var State
     */
    protected $model;

    protected function setUp(): void
    {
        $this->model = new State();
    }

    public function testIsEnabled()
    {
        $this->assertTrue($this->model->isEnabled());

        $this->model->disable();
        $this->assertFalse($this->model->isEnabled());

        $this->model->enable();
        $this->assertTrue($this->model->isEnabled());
    }

    public function testSuspend()
    {
        $this->assertTrue($this->model->isEnabled());

        $this->model->suspend();
        $this->assertFalse($this->model->isEnabled());

        $this->model->suspend(true);
        $this->assertFalse($this->model->isEnabled());
    }

    public function testResume()
    {
        $this->assertTrue($this->model->isEnabled());

        $this->model->resume(null);
        $this->assertNull($this->model->isEnabled());

        $this->model->resume();
        $this->assertNull($this->model->isEnabled());

        $this->model->resume(false);
        $this->assertFalse($this->model->isEnabled());
    }
}
