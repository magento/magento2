<?php declare(strict_types=1);
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Test\Unit\Component\Control;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponent\Processor;
use Magento\Ui\Component\Control\Link;
use PHPUnit\Framework\TestCase;

class LinkTest extends TestCase
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
        $context = $this->getMockBuilder(ContextInterface::class)
            ->getMockForAbstractClass();
        $processor = $this->getMockBuilder(Processor::class)
            ->disableOriginalConstructor()
            ->getMock();
        $context->expects($this->never())->method('getProcessor')->willReturn($processor);

        $this->objectManager = new ObjectManager($this);
        $this->link = $this->objectManager->getObject(
            Link::class,
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
