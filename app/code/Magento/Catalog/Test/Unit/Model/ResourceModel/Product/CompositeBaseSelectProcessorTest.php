<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Model\ResourceModel\Product;

use Magento\Catalog\Model\ResourceModel\Product\BaseSelectProcessorInterface;
use Magento\Catalog\Model\ResourceModel\Product\CompositeBaseSelectProcessor;
use Magento\Framework\DB\Select;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class CompositeBaseSelectProcessorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    protected function setUp()
    {
        $this->objectManager =  new ObjectManager($this);
    }

    /**
     * @expectedException \Magento\Framework\Exception\InputException
     */
    public function testInitializeWithWrongProcessorInstance()
    {
        $processorValid = $this->createMock(BaseSelectProcessorInterface::class);
        $processorInvalid = $this->createMock(\stdClass::class);

        $this->objectManager->getObject(CompositeBaseSelectProcessor::class, [
            'baseSelectProcessors' => [$processorValid, $processorInvalid],
        ]);
    }

    public function testProcess()
    {
        $select = $this->getMockBuilder(Select::class)->disableOriginalConstructor()->getMock();

        $processorFirst = $this->createMock(BaseSelectProcessorInterface::class);
        $processorFirst->expects($this->once())->method('process')->with($select)->willReturn($select);

        $processorSecond = $this->createMock(BaseSelectProcessorInterface::class);
        $processorSecond->expects($this->once())->method('process')->with($select)->willReturn($select);

        /** @var CompositeBaseSelectProcessor $baseSelectProcessors */
        $baseSelectProcessors = $this->objectManager->getObject(CompositeBaseSelectProcessor::class, [
            'baseSelectProcessors' => [$processorFirst, $processorSecond],
        ]);
        $this->assertEquals($select, $baseSelectProcessors->process($select));
    }
}
