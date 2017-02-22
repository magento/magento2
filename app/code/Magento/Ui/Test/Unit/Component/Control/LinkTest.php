<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Test\Unit\Component\Control;

use Magento\Ui\Component\Control\Link;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Class LinkTest
 */
class LinkTest extends \PHPUnit_Framework_TestCase
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
    public function setUp()
    {
        $context = $this->getMockBuilder('Magento\Framework\View\Element\UiComponent\ContextInterface')
            ->getMockForAbstractClass();
        $processor = $this->getMockBuilder('Magento\Framework\View\Element\UiComponent\Processor')
            ->disableOriginalConstructor()
            ->getMock();
        $context->expects($this->any())->method('getProcessor')->willReturn($processor);

        $this->objectManager = new ObjectManager($this);
        $this->link = $this->objectManager->getObject('Magento\Ui\Component\Control\Link', ['context' => $context]);
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
