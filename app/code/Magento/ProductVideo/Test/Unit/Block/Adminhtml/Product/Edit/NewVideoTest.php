<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ProductVideo\Test\Unit\Block\Adminhtml\Product\Edit;

class NewVideoTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Backend\Block\Template\Context|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $contextMock;

    /*
     * @var \PHPUnit\Framework\MockObject\MockObject|\Magento\Framework\UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var \Magento\Framework\Math\Random|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $mathRandom;

    /**
     * @var \Magento\Framework\Registry|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $registryMock;

    /**
     * @var \Magento\Framework\Data\FormFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $formFactoryMock;

    /**
     * @var \Magento\Framework\Json\EncoderInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $jsonEncoderMock;

    /**
     * @var \Magento\ProductVideo\Helper\Media|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $mediaHelper;

    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     * |\Magento\ProductVideo\Block\Adminhtml\Product\Edit\NewVideo
     */
    protected $block;

    protected function setUp(): void
    {
        $this->contextMock = $this->createMock(\Magento\Backend\Block\Template\Context::class);
        $this->mediaHelper = $this->createMock(\Magento\ProductVideo\Helper\Media::class);
        $this->mathRandom = $this->createMock(\Magento\Framework\Math\Random::class);
        $this->urlBuilder = $this->createMock(\Magento\Framework\UrlInterface::class);
        $this->contextMock->expects($this->any())->method('getMathRandom')->willReturn($this->mathRandom);
        $this->contextMock->expects($this->any())->method('getUrlBuilder')->willReturn($this->urlBuilder);
        $this->registryMock = $this->createMock(\Magento\Framework\Registry::class);
        $this->formFactoryMock = $this->createMock(\Magento\Framework\Data\FormFactory::class);
        $this->jsonEncoderMock = $this->createMock(\Magento\Framework\Json\EncoderInterface::class);

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
        $result = $this->block->getHtmlId();
        $this->assertNotNull($result);
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
        $result = $this->block->getWidgetOptions();
        $this->assertNotNull($result);
    }
}
