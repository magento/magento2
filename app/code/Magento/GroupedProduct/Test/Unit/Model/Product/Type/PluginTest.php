<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GroupedProduct\Test\Unit\Model\Product\Type;

class PluginTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $moduleManagerMock;

    /**
     * @var \Magento\GroupedProduct\Model\Product\Type\Plugin
     */
    protected $object;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $subjectMock;

    protected function setUp(): void
    {
        $this->moduleManagerMock = $this->createMock(\Magento\Framework\Module\Manager::class);
        $this->subjectMock = $this->createMock(\Magento\Catalog\Model\Product\Type::class);
        $this->object = new \Magento\GroupedProduct\Model\Product\Type\Plugin($this->moduleManagerMock);
    }

    public function testAfterGetOptionArray()
    {
        $this->moduleManagerMock->expects($this->any())->method('isOutputEnabled')->willReturn(false);
        $this->assertEquals(
            [],
            $this->object->afterGetOptionArray($this->subjectMock, ['grouped' => 'test'])
        );
    }
}
