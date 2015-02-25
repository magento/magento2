<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Model\Config\Source\Dev;

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
        $this->assertInstanceOf('\Magento\Framework\Option\ArrayInterface', $this->model);
        $this->assertCount(3, $this->model->toOptionArray());
        $this->assertCount(3, WorkflowType::$labels);

        $option = current($this->model->toOptionArray());

        $this->assertArrayHasKey('value', $option);
        $this->assertArrayHasKey('label', $option);

        /** @var \Magento\Framework\Phrase $label */
        $label = $option['label'];
        $this->assertInstanceOf('\Magento\Framework\Phrase', $label);
        $this->assertSame(
            WorkflowType::$labels[WorkflowType::CLIENT_SIDE_COMPILATION],
            $label->render()
        );
    }
}
