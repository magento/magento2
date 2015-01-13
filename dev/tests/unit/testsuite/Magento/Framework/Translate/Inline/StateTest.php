<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Translate\Inline;

class StateTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var State
     */
    protected $model;

    protected function setUp()
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
