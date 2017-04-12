<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ProductVideo\Test\Unit\Block\Adminhtml\Product\Edit;

class NewVideoTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Backend\Block\Template\Context|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $contextMock;

    /*
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var \Magento\Framework\Math\Random|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $mathRandom;

    /**
     * @var \Magento\Framework\Registry|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $registryMock;

    /**
     * @var \Magento\Framework\Data\FormFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $formFactoryMock;

    /**
     * @var \Magento\Framework\Json\EncoderInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $jsonEncoderMock;

    /**
     * @var \Magento\ProductVideo\Helper\Media|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $mediaHelper;

    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     * |\Magento\ProductVideo\Block\Adminhtml\Product\Edit\NewVideo
     */
    protected $block;

    protected function setUp()
    {
        $this->contextMock = $this->getMock(\Magento\Backend\Block\Template\Context::class, [], [], '', false);
        $this->mediaHelper = $this->getMock(\Magento\ProductVideo\Helper\Media::class, [], [], '', false);
        $this->mathRandom = $this->getMock(\Magento\Framework\Math\Random::class, [], [], '', false);
        $this->urlBuilder = $this->getMock(\Magento\Framework\UrlInterface::class, [], [], '', false);
        $this->contextMock->expects($this->any())->method('getMathRandom')->willReturn($this->mathRandom);
        $this->contextMock->expects($this->any())->method('getUrlBuilder')->willReturn($this->urlBuilder);
        $this->registryMock = $this->getMock(\Magento\Framework\Registry::class, [], [], '', false);
        $this->formFactoryMock = $this->getMock(\Magento\Framework\Data\FormFactory::class, [], [], '', false);
        $this->jsonEncoderMock = $this->getMock(\Magento\Framework\Json\EncoderInterface::class, [], [], '', false);

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->block = $objectManager->getObject(
            \Magento\ProductVideo\Block\Adminhtml\Product\Edit\NewVideo::class,
            [
                'context' => $this->contextMock,
                'mediaHelper' => $this->mediaHelper,
                'urlBuilder' => $this->urlBuilder,
                'jsonEncoder' => $this->jsonEncoderMock,
                'registry' => $this->registryMock,
                'formFactory' => $this->formFactoryMock,
            ]
        );
    }

    public function testGetHtmlId()
    {
        $this->mathRandom->expects($this->any())->method('getUniqueHash')->with('id_')->willReturn('id_' . rand());
        $this->block->getHtmlId();
    }

    public function testGetWidgetOptions()
    {
        $rand = rand();
        $this->mathRandom->expects($this->any())->method('getUniqueHash')->with('id_')->willReturn('id_' . $rand);
        $saveVideoUrl = 'http://host/index.php/admin/catalog/product_gallery/upload/key/';
        $saveRemoteVideoUrl = 'http://host/index.php/admin/product_video/product_gallery/retrieveImage/';
        $this->urlBuilder->expects($this->exactly(2))->method('getUrl')->willReturnOnConsecutiveCalls(
            $saveVideoUrl,
            $saveRemoteVideoUrl
        );
        $value = [
            'saveVideoUrl' => $saveVideoUrl,
            'saveRemoteVideoUrl' => $saveRemoteVideoUrl,
            'htmlId' => 'id_' . $rand,
            'youTubeApiKey' => null,
            'videoSelector' => '#media_gallery_content'
        ];
        $this->jsonEncoderMock->expects($this->once())->method('encode')->with(
            $value
        )->willReturn(
            json_encode($value)
        );
        $this->block->getWidgetOptions();
    }
}
