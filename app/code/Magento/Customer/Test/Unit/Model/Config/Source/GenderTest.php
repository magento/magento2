<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Test\Unit\Model\Config\Source;

use Magento\Customer\Model\Config\Source\Gender;

class GenderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Gender
     */
    protected $model;

    protected function setUp()
    {
        $this->model = new Gender();
    }

    public function testInstance()
    {
        $this->assertInstanceOf('Magento\Framework\Option\ArrayInterface', $this->model);
    }

    public function testToOptionArray()
    {
        $result = $this->model->toOptionArray();
        $this->assertTrue(is_array($result));
        $this->assertCount(2, $result);

        $this->assertArrayHasKey('label', $result[0]);
        $this->assertArrayHasKey('value', $result[0]);

        $this->assertArrayHasKey('label', $result[1]);
        $this->assertArrayHasKey('value', $result[1]);
    }
}
