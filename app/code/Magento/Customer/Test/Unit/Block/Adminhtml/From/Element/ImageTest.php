<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Test\Unit\Block\Adminhtml\From\Element;

use Magento\Backend\Helper\Data;
use Magento\Customer\Block\Adminhtml\Form\Element\Image;
use Magento\Framework\Data\Form;
use Magento\Framework\Math\Random;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\Url\EncoderInterface;
use Magento\Framework\View\Helper\SecureHtmlRenderer;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test class for \Magento\Customer\Block\Adminhtml\From\Element\Image
 */
class ImageTest extends TestCase
{
    /**
     * @var Image
     */
    protected $image;

    /**
     * @var MockObject
     */
    protected $backendHelperMock;

    /**
     * @var EncoderInterface|MockObject
     */
    protected $urlEncoder;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $objects = [
            [
                SecureHtmlRenderer::class,
                $this->createMock(SecureHtmlRenderer::class)
            ],
            [
                Random::class,
                $this->createMock(Random::class)
            ]
        ];
        $objectManager->prepareObjectManager($objects);
        $this->backendHelperMock = $this->getMockBuilder(Data::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->urlEncoder = $this->getMockBuilder(EncoderInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->image = $objectManager->getObject(
            Image::class,
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
        $formMock = $this->getMockBuilder(Form::class)
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
            ->with('customer/index/viewfile', ['file' => $value])
            ->willReturn($url);

        $this->assertStringContainsString($url, $this->image->getElementHtml());
    }
}
