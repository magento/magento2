<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Test\Unit\Component\Form\Element\DataType;

use Magento\Ui\Component\Form\Element\DataType\Media;

class MediaTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Framework\View\Element\UiComponent\ContextInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $context;

    /** @var \Magento\Framework\UrlInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $urlBuilder;

    /** @var \Magento\Framework\View\Element\UiComponent\Processor|\PHPUnit_Framework_MockObject_MockObject */
    protected $processor;

    /** @var Media */
    protected $media;

    public function setUp()
    {
        $this->context = $this->getMockBuilder(\Magento\Framework\View\Element\UiComponent\ContextInterface::class)
            ->getMockForAbstractClass();
        $this->urlBuilder = $this->getMockBuilder(\Magento\Framework\UrlInterface::class)
            ->getMockForAbstractClass();
        $this->processor = $this->getMockBuilder(\Magento\Framework\View\Element\UiComponent\Processor::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->context->expects($this->any())
            ->method('getProcessor')
            ->willReturn($this->processor);

        $this->media = new Media($this->context);
        $this->media->setData(
            [
                'config' => [
                    'uploaderConfig' => [
                        'url' => 'module/actionPath/path'
                    ],
                ],
            ]
        );
    }

    public function testPrepare()
    {
        $url = 'http://magento2.com/module/actionPath/path/key/34523456234523trdg';
        $this->context->expects($this->once())
            ->method('getUrl')
            ->with('module/actionPath/path', ['_secure' => true])
            ->willReturn($url);
        $this->media->prepare();
    }
}
