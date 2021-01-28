<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Block\Product\View;

use Magento\Catalog\Block\Product\Context;
use Magento\Catalog\Block\Product\View\Gallery;
use Magento\Catalog\Block\Product\View\GalleryOptions;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\Escaper;
use Magento\Framework\View\Config;
use Magento\Framework\Config\View;
use Magento\Framework\Serialize\Serializer\Json;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class GalleryOptionsTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var GalleryOptions
     */
    private $model;

    /**
     * @var Gallery|\PHPUnit\Framework\MockObject\MockObject
     */
    private $gallery;

    /**
     * @var Context|\PHPUnit\Framework\MockObject\MockObject
     */
    private $context;

    /**
     * @var Json
     */
    private $jsonSerializer;

    /**
     * @var View|\PHPUnit\Framework\MockObject\MockObject
     */
    private $configView;

    /**
     * @var Config|\PHPUnit\Framework\MockObject\MockObject
     */
    private $viewConfig;

    /**
     * @var Escaper
     */
    private $escaper;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);

        $this->escaper = $objectManager->getObject(Escaper::class);
        $this->configView = $this->createMock(View::class);

        $this->viewConfig = $this->createConfiguredMock(
            Config::class,
            [
                'getViewConfig' => $this->configView
            ]
        );

        $this->context = $this->createConfiguredMock(
            Context::class,
            [
                'getEscaper' => $this->escaper,
                'getViewConfig' => $this->viewConfig
            ]
        );

        $this->gallery = $this->createMock(Gallery::class);

        $this->jsonSerializer = $objectManager->getObject(
            Json::class
        );

        $this->model =  $objectManager->getObject(GalleryOptions::class, [
            'context' => $this->context,
            'jsonSerializer' => $this->jsonSerializer,
            'gallery' => $this->gallery
        ]);
    }

    public function testGetOptionsJson()
    {
        $configMap = [
            ['Magento_Catalog', 'gallery/nav', 'thumbs'],
            ['Magento_Catalog', 'gallery/loop', false],
            ['Magento_Catalog', 'gallery/keyboard', true],
            ['Magento_Catalog', 'gallery/arrows', true],
            ['Magento_Catalog', 'gallery/caption', false],
            ['Magento_Catalog', 'gallery/allowfullscreen', true],
            ['Magento_Catalog', 'gallery/navdir', 'horizontal'],
            ['Magento_Catalog', 'gallery/navarrows', true],
            ['Magento_Catalog', 'gallery/navtype', 'slides'],
            ['Magento_Catalog', 'gallery/thumbmargin', '5'],
            ['Magento_Catalog', 'gallery/transition/effect', 'slide'],
            ['Magento_Catalog', 'gallery/transition/duration', '500'],
        ];

        $imageAttributesMap = [
            ['product_page_image_medium','height',null, 100],
            ['product_page_image_medium','width',null, 200],
            ['product_page_image_small','height',null, 300],
            ['product_page_image_small','width',null, 400]
        ];

        $this->configView->expects($this->any())
            ->method('getVarValue')
            ->willReturnMap($configMap);
        $this->gallery->expects($this->any())
            ->method('getImageAttribute')
            ->willReturnMap($imageAttributesMap);

        $json = $this->model->getOptionsJson();

        $decodedJson = $this->jsonSerializer->unserialize($json);

        $this->assertSame('thumbs', $decodedJson['nav']);
        $this->assertFalse($decodedJson['loop']);
        $this->assertTrue($decodedJson['keyboard']);
        $this->assertTrue($decodedJson['arrows']);
        $this->assertFalse($decodedJson['showCaption']);
        $this->assertTrue($decodedJson['allowfullscreen']);
        $this->assertSame('horizontal', $decodedJson['navdir']);
        $this->assertTrue($decodedJson['navarrows']);
        $this->assertSame('slides', $decodedJson['navtype']);
        $this->assertSame(5, $decodedJson['thumbmargin']);
        $this->assertSame('slide', $decodedJson['transition']);
        $this->assertSame(500, $decodedJson['transitionduration']);
        $this->assertSame(100, $decodedJson['height']);
        $this->assertSame(200, $decodedJson['width']);
        $this->assertSame(300, $decodedJson['thumbheight']);
        $this->assertSame(400, $decodedJson['thumbwidth']);
    }

    public function testGetFSOptionsJson()
    {
        $configMap = [
            ['Magento_Catalog', 'gallery/fullscreen/nav', false],
            ['Magento_Catalog', 'gallery/fullscreen/loop', true],
            ['Magento_Catalog', 'gallery/fullscreen/keyboard', true],
            ['Magento_Catalog', 'gallery/fullscreen/arrows', false],
            ['Magento_Catalog', 'gallery/fullscreen/caption', true],
            ['Magento_Catalog', 'gallery/fullscreen/navdir', 'vertical'],
            ['Magento_Catalog', 'gallery/fullscreen/navarrows', false],
            ['Magento_Catalog', 'gallery/fullscreen/navtype', 'thumbs'],
            ['Magento_Catalog', 'gallery/fullscreen/thumbmargin', '10'],
            ['Magento_Catalog', 'gallery/fullscreen/transition/effect', 'dissolve'],
            ['Magento_Catalog', 'gallery/fullscreen/transition/duration', '300']
        ];

        $this->configView->expects($this->any())
            ->method('getVarValue')
            ->willReturnMap($configMap);

        $json = $this->model->getFSOptionsJson();

        $decodedJson = $this->jsonSerializer->unserialize($json);

        //Note, this tests the special case for nav variable set to false. It
        //Should not be converted to boolean.
        $this->assertSame('false', $decodedJson['nav']);
        $this->assertTrue($decodedJson['loop']);
        $this->assertFalse($decodedJson['arrows']);
        $this->assertTrue($decodedJson['keyboard']);
        $this->assertTrue($decodedJson['showCaption']);
        $this->assertSame('vertical', $decodedJson['navdir']);
        $this->assertFalse($decodedJson['navarrows']);
        $this->assertSame(10, $decodedJson['thumbmargin']);
        $this->assertSame('thumbs', $decodedJson['navtype']);
        $this->assertSame('dissolve', $decodedJson['transition']);
        $this->assertSame(300, $decodedJson['transitionduration']);
    }

    public function testGetOptionsJsonOptionals()
    {
        $configMap = [
            ['Magento_Catalog', 'gallery/fullscreen/thumbmargin', false],
            ['Magento_Catalog', 'gallery/fullscreen/transition/duration', false]
        ];

        $this->configView->expects($this->any())
            ->method('getVarValue')
            ->willReturnMap($configMap);

        $json = $this->model->getOptionsJson();

        $decodedJson = $this->jsonSerializer->unserialize($json);

        $this->assertArrayNotHasKey('thumbmargin', $decodedJson);
        $this->assertArrayNotHasKey('transitionduration', $decodedJson);
    }

    public function testGetFSOptionsJsonOptionals()
    {
        $configMap = [
            ['Magento_Catalog', 'gallery/fullscreen/keyboard', false],
            ['Magento_Catalog', 'gallery/fullscreen/thumbmargin', false],
            ['Magento_Catalog', 'gallery/fullscreen/transition/duration', false]
        ];

        $this->configView->expects($this->any())
            ->method('getVarValue')
            ->willReturnMap($configMap);

        $json = $this->model->getFSOptionsJson();

        $decodedJson = $this->jsonSerializer->unserialize($json);

        $this->assertArrayNotHasKey('thumbmargin', $decodedJson);
        $this->assertArrayNotHasKey('keyboard', $decodedJson);
        $this->assertArrayNotHasKey('transitionduration', $decodedJson);
    }
}
