<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GiftMessage\Test\Unit\Block\Message;

class InlineTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\GiftMessage\Block\Message\Inline
     */
    protected $block;

    /**
     * @var \Magento\Framework\View\Element\Template\Context|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $context;

    /**
     * @var \Magento\Customer\Model\Session|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $session;

    /**
     * @var \Magento\GiftMessage\Helper\Message|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $messageHelper;

    /**
     * @var \Magento\Catalog\Block\Product\ImageBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $imageBuilder;

    /**
     * @var \Magento\Framework\App\Http\Context|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $httpContext;

    protected function setUp()
    {
        $this->context = $this->getMockBuilder('Magento\Framework\View\Element\Template\Context')
            ->disableOriginalConstructor()
            ->getMock();

        $this->session = $this->getMockBuilder('Magento\Customer\Model\Session')
            ->disableOriginalConstructor()
            ->getMock();

        $this->messageHelper = $this->getMockBuilder('Magento\GiftMessage\Helper\Message')
            ->disableOriginalConstructor()
            ->getMock();

        $this->imageBuilder = $this->getMockBuilder('Magento\Catalog\Block\Product\ImageBuilder')
            ->disableOriginalConstructor()
            ->getMock();

        $this->httpContext = $this->getMockBuilder('Magento\Framework\App\Http\Context')
            ->disableOriginalConstructor()
            ->getMock();

        $this->block = new \Magento\GiftMessage\Block\Message\Inline(
            $this->context,
            $this->session,
            $this->messageHelper,
            $this->imageBuilder,
            $this->httpContext
        );
    }

    public function testGetImage()
    {
        $imageId = 'test_image_id';
        $attributes = [];

        $productMock = $this->getMockBuilder('Magento\Catalog\Model\Product')
            ->disableOriginalConstructor()
            ->getMock();

        $imageMock = $this->getMockBuilder('Magento\Catalog\Block\Product\Image')
            ->disableOriginalConstructor()
            ->getMock();

        $this->imageBuilder->expects($this->once())
            ->method('setProduct')
            ->with($productMock)
            ->willReturnSelf();
        $this->imageBuilder->expects($this->once())
            ->method('setImageId')
            ->with($imageId)
            ->willReturnSelf();
        $this->imageBuilder->expects($this->once())
            ->method('setAttributes')
            ->with($attributes)
            ->willReturnSelf();
        $this->imageBuilder->expects($this->once())
            ->method('create')
            ->willReturn($imageMock);

        $this->assertInstanceOf(
            'Magento\Catalog\Block\Product\Image',
            $this->block->getImage($productMock, $imageId, $attributes)
        );
    }
}
