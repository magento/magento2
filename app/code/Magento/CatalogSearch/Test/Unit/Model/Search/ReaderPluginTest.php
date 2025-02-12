<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogSearch\Test\Unit\Model\Search;

use Magento\CatalogSearch\Model\Search\ReaderPlugin;
use Magento\CatalogSearch\Model\Search\Request\ModifierInterface;
use Magento\Framework\Config\ReaderInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ReaderPluginTest extends TestCase
{
    /** @var ModifierInterface|MockObject */
    protected $requestModifier;

    /** @var ObjectManager  */
    protected $objectManagerHelper;

    /** @var ReaderPlugin */
    protected $object;

    protected function setUp(): void
    {
        $this->requestModifier = $this->getMockBuilder(ModifierInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->objectManagerHelper = new ObjectManager($this);
        $this->object = new ReaderPlugin($this->requestModifier);
    }

    public function testAfterRead()
    {
        $readerConfig = ['test' => 'b', 'd' => 'e'];
        $this->requestModifier->expects($this->once())
            ->method('modify')
            ->with($readerConfig)
            ->willReturn(['test' => 'a']);

        $result = $this->object->afterRead(
            $this->getMockBuilder(ReaderInterface::class)
                ->disableOriginalConstructor()
                ->getMockForAbstractClass(),
            $readerConfig,
            null
        );

        $this->assertEquals(['test' => 'a'], $result);
    }
}
