<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Developer\Test\Unit\Model\Config\Source;

use Magento\Developer\Model\Config\Source\WorkflowType;

/**
 * Class WorkflowTypeTest
 *
 * @package Magento\Backend\Model\Config\Source\Dev
 */
class WorkflowTypeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var WorkflowType
     */
    protected $model;

    protected function setUp()
    {
        $this->model = new WorkflowType();
    }

    public function testToOptionArray()
    {
        $this->assertInstanceOf(\Magento\Framework\Option\ArrayInterface::class, $this->model);
        $this->assertCount(2, $this->model->toOptionArray());
        $option = current($this->model->toOptionArray());

        /** @var \Magento\Framework\Phrase $label */
        $label = $option['label'];
        $this->assertInstanceOf(\Magento\Framework\Phrase::class, $label);
    }

    public function testOptionStructure()
    {
        foreach ($this->model->toOptionArray() as $option) {
            $this->assertArrayHasKey('value', $option);
            $this->assertArrayHasKey('label', $option);
        }
    }
}
