<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Config\Test\Unit\Model\Config\Source;

use Magento\Config\Model\Config\Source\Yesno;

/**
 * Test class for \Magento\Config\Model\Config\Source\Yesno
 */
class YesNoTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Yesno
     */
    private $model;

    /**
     * @var null|array
     */
    private $expectedToOptionArray = null;

    protected function setUp()
    {
        $this->model = new Yesno();
    }

    /**
     * @return array
     */
    private function getExpectedToOptionArray()
    {
        if ($this->expectedToOptionArray === null) {
            $this->expectedToOptionArray = [
                ['value' => 1, 'label' => __('Yes')],
                ['value' => 0, 'label' => __('No')]
            ];
        }

        return $this->expectedToOptionArray;
    }

    public function testToOptionArray()
    {
        $expected = $this->getExpectedToOptionArray();
        $this->assertEquals($expected, $this->model->toOptionArray());
    }

    /**
     * @depends testToOptionArray
     */
    public function testToOptionArrayTwice()
    {
        $expected = $this->getExpectedToOptionArray();
        $this->assertEquals($expected, $this->model->toOptionArray());
    }

    public function testToArray()
    {
        $expected = [0 => __('No'), 1 => __('Yes')];
        $this->assertEquals($expected, $this->model->toArray());
    }
}
