<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Test\Unit\Component\Control;

use Magento\Ui\Component\Control\Action;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Class ActionTest
 */
class ActionTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Action
     */
    protected $action;

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
        $this->action = $this->objectManager->getObject(
            \Magento\Ui\Component\Control\Action::class,
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
        $this->assertTrue($this->action->getComponentName() === Action::NAME);
    }
}
