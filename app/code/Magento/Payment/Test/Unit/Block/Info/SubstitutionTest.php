<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Payment\Test\Unit\Block\Info;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SubstitutionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $layout;

    /**
     * @var \Magento\Payment\Block\Info\Substitution
     */
    protected $block;

    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $objectManager;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->layout = $this->getMockBuilder(\Magento\Framework\View\LayoutInterface::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $eventManager = $this->getMockBuilder(\Magento\Framework\Event\ManagerInterface::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $context = $this->getMockBuilder(\Magento\Framework\View\Element\Template\Context::class)
            ->disableOriginalConstructor()
            ->setMethods(['getLayout', 'getEventManager', 'getScopeConfig'])
            ->getMock();
        $context->expects($this->any())
            ->method('getLayout')
            ->willReturn($this->layout);
        $context->expects($this->any())->method('getEventManager')
            ->willReturn($eventManager);

        $this->block = $this->objectManager->getObject(
            \Magento\Payment\Block\Info\Substitution::class,
            [
                'context' => $context,
                'data' => [
                    'template' => null,
                ]
            ]
        );
    }

    public function testBeforeToHtml()
    {
        $abstractBlock = $this->getMockBuilder(\Magento\Framework\View\Element\AbstractBlock::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $childAbstractBlock = clone $abstractBlock;

        $abstractBlock->expects($this->any())
            ->method('getParentBlock')
            ->willReturn($childAbstractBlock);
        $this->layout->expects($this->any())
            ->method('getParentName')
            ->willReturn('parentName');
        $this->layout->expects($this->any())
            ->method('getBlock')
            ->willReturn($abstractBlock);

        $infoMock = $this->getMockBuilder(\Magento\Payment\Model\Info::class)
            ->disableOriginalConstructor()
            ->getMock();
        $methodMock = $this->getMockBuilder(\Magento\Payment\Model\MethodInterface::class)
            ->getMockForAbstractClass();
        $infoMock->expects($this->once())
            ->method('getMethodInstance')
            ->willReturn($methodMock);

        $this->block->setInfo($infoMock);

        $fakeBlock = new \StdClass();
        $this->layout->expects($this->any())
            ->method('createBlock')
            ->with(
                \Magento\Framework\View\Element\Template::class,
                '',
                ['data' => ['method' => $methodMock, 'template' => 'Magento_Payment::info/substitution.phtml']]
            )->willReturn($fakeBlock);

        $childAbstractBlock->expects($this->any())
            ->method('setChild')
            ->with('order_payment_additional', $fakeBlock);

        $this->block->toHtml();
    }
}
