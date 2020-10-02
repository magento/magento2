<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogSearch\Test\Unit\Model\Search;

use Magento\CatalogSearch\Model\Search\ReaderPlugin;
use Magento\CatalogSearch\Model\Search\RequestGenerator;
use Magento\Framework\Config\ReaderInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ReaderPluginTest extends TestCase
{
    /** @var RequestGenerator|MockObject */
    protected $requestGenerator;

    /** @var ObjectManager  */
    protected $objectManagerHelper;

    /** @var ReaderPlugin */
    protected $object;

    protected function setUp(): void
    {
        $this->requestGenerator = $this->getMockBuilder(RequestGenerator::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->objectManagerHelper = new ObjectManager($this);
        $this->object = $this->objectManagerHelper->getObject(
            ReaderPlugin::class,
            ['requestGenerator' => $this->requestGenerator]
        );
    }

    public function testAfterRead()
    {
        $readerConfig = ['test' => 'b', 'd' => 'e'];
        $this->requestGenerator->expects($this->once())
            ->method('generate')
            ->willReturn(['test' => 'a']);

        $result = $this->object->afterRead(
            $this->getMockBuilder(ReaderInterface::class)
                ->disableOriginalConstructor()
                ->getMockForAbstractClass(),
            $readerConfig,
            null
        );

        $this->assertEquals(['test' => ['b', 'a'], 'd' => 'e'], $result);
    }
}
