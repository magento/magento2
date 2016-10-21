<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Model\ResourceModel\Product;

use Magento\Catalog\Model\ResourceModel\Product\BaseSelectProcessorInterface;
use Magento\Catalog\Model\ResourceModel\Product\CompositeBaseSelectProcessor;
use Magento\Framework\DB\Select;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class CompositeBaseSelectProcessorTest extends \PHPUnit_Framework_TestCase
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
        $processorValid = $this->getMock(BaseSelectProcessorInterface::class);
        $processorInvalid = $this->getMock(\stdClass::class);

        $this->objectManager->getObject(CompositeBaseSelectProcessor::class, [
            'baseSelectProcessors' => [$processorValid, $processorInvalid],
        ]);
    }

    public function testProcess()
    {
        $select = $this->getMockBuilder(Select::class)->disableOriginalConstructor()->getMock();

        $processorFirst = $this->getMock(BaseSelectProcessorInterface::class);
        $processorFirst->expects($this->once())->method('process')->with($select)->willReturn($select);

        $processorSecond = $this->getMock(BaseSelectProcessorInterface::class);
        $processorSecond->expects($this->once())->method('process')->with($select)->willReturn($select);

        /** @var CompositeBaseSelectProcessor $baseSelectProcessors */
        $baseSelectProcessors = $this->objectManager->getObject(CompositeBaseSelectProcessor::class, [
            'baseSelectProcessors' => [$processorFirst, $processorSecond],
        ]);
        $this->assertEquals($select, $baseSelectProcessors->process($select));
    }
}
