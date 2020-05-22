<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GroupedProduct\Test\Unit\Model\Product\Type;

use Magento\Catalog\Model\Product\Type;
use Magento\Framework\Module\Manager;
use Magento\GroupedProduct\Model\Product\Type\Plugin;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class PluginTest extends TestCase
{
    /**
     * @var MockObject
     */
    protected $moduleManagerMock;

    /**
     * @var Plugin
     */
    protected $object;

    /**
     * @var MockObject
     */
    protected $subjectMock;

    protected function setUp(): void
    {
        $this->moduleManagerMock = $this->createMock(Manager::class);
        $this->subjectMock = $this->createMock(Type::class);
        $this->object = new Plugin($this->moduleManagerMock);
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
