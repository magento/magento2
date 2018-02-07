<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GroupedProduct\Test\Unit\Model\Product\Type;

class PluginTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $moduleManagerMock;

    /**
     * @var \Magento\GroupedProduct\Model\Product\Type\Plugin
     */
    protected $object;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $subjectMock;

    protected function setUp()
    {
        $this->moduleManagerMock = $this->getMock('\Magento\Framework\Module\Manager', [], [], '', false);
        $this->subjectMock = $this->getMock('Magento\Catalog\Model\Product\Type', [], [], '', false);
        $this->object = new \Magento\GroupedProduct\Model\Product\Type\Plugin($this->moduleManagerMock);
    }

    public function testAfterGetOptionArray()
    {
        $this->moduleManagerMock->expects($this->any())->method('isOutputEnabled')->will($this->returnValue(false));
        $this->assertEquals(
            [],
            $this->object->afterGetOptionArray($this->subjectMock, ['grouped' => 'test'])
        );
    }
}
