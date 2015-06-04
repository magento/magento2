<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GiftMessage\Test\Unit\Block\Cart;

use Magento\GiftMessage\Block\Cart\GiftOptions;

class GiftOptionsTest extends \PHPUnit_Framework_TestCase
{
    /** @var  \Magento\Backend\Block\Template\Context|\PHPUnit_Framework_MockObject_MockObject */
    protected $context;

    /** @var \Magento\Checkout\Model\CompositeConfigProvider|\PHPUnit_Framework_MockObject_MockObject */
    protected $compositeConfigProvider;

    /** @var \Magento\Checkout\Model\CompositeConfigProvider|\PHPUnit_Framework_MockObject_MockObject */
    protected $layoutProcessor;

    /** @var \Magento\GiftMessage\Block\Cart\GiftOptions */
    protected $object;

    /** @var \Magento\Framework\Json\Encoder|\PHPUnit_Framework_MockObject_MockObject */
    protected $jsonEncoder;

    public function setUp()
    {
        $this->context = $this->getMock('Magento\Backend\Block\Template\Context', [], [], '', false);
        $this->jsonEncoder = $this->getMock('Magento\Framework\Json\Encoder', [], [], '', false);
        $this->compositeConfigProvider = $this->getMock(
            'Magento\Checkout\Model\CompositeConfigProvider',
            [],
            [],
            '',
            false
        );
        $this->layoutProcessor = $this->getMockForAbstractClass(
            'Magento\Checkout\Block\Checkout\LayoutProcessorInterface',
            [],
            '',
            false
        );
        $this->object =  new GiftOptions(
            $this->context,
            $this->jsonEncoder,
            $this->compositeConfigProvider,
            [$this->layoutProcessor],
            ['jsLayout' => []]
        );
    }

    public function testGetJsLayout()
    {
        $this->layoutProcessor->expects($this->once())
            ->method('process')
            ->willReturn([]);
        $this->jsonEncoder->expects($this->once())
            ->method('encode')
            ->willReturn('[]');
        $this->object->getJsLayout();
    }

    public function testGetGiftOptionsConfigJson()
    {
        $this->compositeConfigProvider->expects($this->once())
            ->method('getConfig')
            ->willReturn([]);
        $this->jsonEncoder->expects($this->once())
            ->method('encode')
            ->willReturn('[]');
        $this->object->getGiftMessageConfigJson();
    }
}
