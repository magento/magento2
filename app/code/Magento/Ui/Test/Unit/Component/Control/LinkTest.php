<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Test\Unit\Component\Control;

use Magento\Ui\Component\Control\Link;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Class LinkTest
 */
class LinkTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Link
     */
    protected $link;

    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * Set up
     */
    protected function setUp(): void
    {
        $context = $this->getMockBuilder(\Magento\Framework\View\Element\UiComponent\ContextInterface::class)
            ->getMockForAbstractClass();
        $processor = $this->getMockBuilder(\Magento\Framework\View\Element\UiComponent\Processor::class)
            ->disableOriginalConstructor()
            ->getMock();
        $context->expects($this->never())->method('getProcessor')->willReturn($processor);

        $this->objectManager = new ObjectManager($this);
        $this->link = $this->objectManager->getObject(
            \Magento\Ui\Component\Control\Link::class,
            ['context' => $context]
        );
    }

    /**
     * Run test getComponentName method
     *
     * @return void
     */
    public function testGetComponentName()
    {
        $this->assertTrue($this->link->getComponentName() === Link::NAME);
    }
}
