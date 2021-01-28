<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Test\Unit\Block\Adminhtml\From\Element;

/**
 * Test class for \Magento\Customer\Block\Adminhtml\From\Element\Image
 */
class ImageTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Customer\Block\Adminhtml\Form\Element\Image
     */
    protected $image;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $backendHelperMock;

    /**
     * @var \Magento\Framework\Url\EncoderInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $urlEncoder;

    protected function setUp(): void
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->backendHelperMock = $this->getMockBuilder(\Magento\Backend\Helper\Data::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->urlEncoder = $this->getMockBuilder(\Magento\Framework\Url\EncoderInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->image = $objectManager->getObject(
            \Magento\Customer\Block\Adminhtml\Form\Element\Image::class,
            [
                'adminhtmlData' => $this->backendHelperMock,
                'urlEncoder' => $this->urlEncoder,
            ]
        );
    }

    public function testGetPreviewFile()
    {
        $value = 'image.jpg';
        $url = 'http://example.com/backend/customer/index/viewfile/' . $value;
        $formMock = $this->getMockBuilder(\Magento\Framework\Data\Form::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->image->setForm($formMock);
        $this->image->setValue($value);

        $this->urlEncoder->expects($this->once())
            ->method('encode')
            ->with($value)
            ->willReturnArgument(0);
        $this->backendHelperMock->expects($this->once())
            ->method('getUrl')
            ->with('customer/index/viewfile', ['image' => $value])
            ->willReturn($url);

        $this->assertStringContainsString($url, $this->image->getElementHtml());
    }
}
