<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ProductVideo\Test\Unit\Block\Adminhtml\Product\Edit;

use Magento\Backend\Block\Template\Context;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Json\EncoderInterface;
use Magento\Framework\Math\Random;
use Magento\Framework\Registry;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\UrlInterface;
use Magento\ProductVideo\Block\Adminhtml\Product\Edit\NewVideo;
use Magento\ProductVideo\Helper\Media;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class NewVideoTest extends TestCase
{
    /**
     * @var Context|MockObject
     */
    protected $contextMock;

    /**
     * @var MockObject|UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var Random|MockObject
     */
    protected $mathRandom;

    /**
     * @var Registry|MockObject
     */
    protected $registryMock;

    /**
     * @var FormFactory|MockObject
     */
    protected $formFactoryMock;

    /**
     * @var EncoderInterface|MockObject
     */
    protected $jsonEncoderMock;

    /**
     * @var Media|MockObject
     */
    protected $mediaHelper;

    /**
     * @var ObjectManager
     * |\Magento\ProductVideo\Block\Adminhtml\Product\Edit\NewVideo
     */
    protected $block;

    protected function setUp(): void
    {
        $this->contextMock = $this->createMock(Context::class);
        $this->mediaHelper = $this->createMock(Media::class);
        $this->mathRandom = $this->createMock(Random::class);
        $this->urlBuilder = $this->getMockForAbstractClass(UrlInterface::class);
        $this->contextMock->expects($this->any())->method('getMathRandom')->willReturn($this->mathRandom);
        $this->contextMock->expects($this->any())->method('getUrlBuilder')->willReturn($this->urlBuilder);
        $this->registryMock = $this->createMock(Registry::class);
        $this->formFactoryMock = $this->createMock(FormFactory::class);
        $this->jsonEncoderMock = $this->getMockForAbstractClass(EncoderInterface::class);

        $objectManager = new ObjectManager($this);

        $this->block = $objectManager->getObject(
            NewVideo::class,
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
