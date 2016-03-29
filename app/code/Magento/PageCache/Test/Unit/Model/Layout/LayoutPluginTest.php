<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\PageCache\Test\Unit\Model\Layout;

class LayoutPluginTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\PageCache\Model\Layout\LayoutPlugin
     */
    protected $model;

    /**
     * @var \Magento\Framework\App\ResponseInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $responseMock;

    /**
     * @var \Magento\Framework\View\Layout|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $layoutMock;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $configMock;

    protected function setUp()
    {
        $this->layoutMock = $this->getMockForAbstractClass(
            'Magento\Framework\View\Layout',
            [],
            '',
            false,
            true,
            true,
            ['isCacheable', 'getAllBlocks', 'getBlock', 'getUpdate', 'getHandles']
        );
        $this->responseMock = $this->getMock('\Magento\Framework\App\Response\Http', [], [], '', false);
        $this->configMock = $this->getMock('Magento\PageCache\Model\Config', [], [], '', false);

        $this->model = new \Magento\PageCache\Model\Layout\LayoutPlugin(
            $this->responseMock,
            $this->configMock
        );
    }

    /**
     * @param $cacheState
     * @param $layoutIsCacheable
     * @dataProvider afterGenerateXmlDataProvider
     */
    public function testAfterGenerateXml($cacheState, $layoutIsCacheable)
    {
        $maxAge = 180;
        $result = 'test';

        $this->layoutMock->expects($this->once())->method('isCacheable')->will($this->returnValue($layoutIsCacheable));
        $this->configMock->expects($this->any())->method('isEnabled')->will($this->returnValue($cacheState));

        if ($layoutIsCacheable && $cacheState) {
            $this->configMock->expects($this->once())->method('getTtl')->will($this->returnValue($maxAge));
            $this->responseMock->expects($this->once())->method('setPublicHeaders')->with($maxAge);
        } else {
            $this->responseMock->expects($this->never())->method('setPublicHeaders');
        }
        $output = $this->model->afterGenerateXml($this->layoutMock, $result);
        $this->assertSame($result, $output);
    }

    public function afterGenerateXmlDataProvider()
    {
        return [
            'Full_cache state is true, Layout is cache-able' => [true, true],
            'Full_cache state is true, Layout is not cache-able' => [true, false],
            'Full_cache state is false, Layout is not cache-able' => [false, false],
            'Full_cache state is false, Layout is cache-able' => [false, true]
        ];
    }

    /**
     * @param $cacheState
     * @param $layoutIsCacheable
     * @param $expectedTags
     * @param $configCacheType
     * @param $ttl
     * @dataProvider afterGetOutputDataProvider
     */
    public function testAfterGetOutput($cacheState, $layoutIsCacheable, $expectedTags, $configCacheType, $ttl)
    {
        $html = 'html';
        $this->configMock->expects($this->any())->method('isEnabled')->will($this->returnValue($cacheState));
        $blockStub = $this->getMock('Magento\PageCache\Test\Unit\Block\Controller\StubBlock', null, [], '', false);
        $blockStub->setTtl($ttl);
        $this->layoutMock->expects($this->once())->method('isCacheable')->will($this->returnValue($layoutIsCacheable));
        $this->layoutMock->expects($this->any())->method('getAllBlocks')->will($this->returnValue([$blockStub]));

        $this->configMock->expects($this->any())->method('getType')->will($this->returnValue($configCacheType));

        if ($layoutIsCacheable && $cacheState) {
            $this->responseMock->expects($this->once())->method('setHeader')->with('X-Magento-Tags', $expectedTags);
        } else {
            $this->responseMock->expects($this->never())->method('setHeader');
        }
        $output = $this->model->afterGetOutput($this->layoutMock, $html);
        $this->assertSame($output, $html);
    }

    public function afterGetOutputDataProvider()
    {
        $tags = 'identity1,identity2';
        return [
            'Cacheable layout, Full_cache state is true' => [true, true, $tags, null, 0],
            'Non-cacheable layout' => [true, false, null, null, 0],
            'Cacheable layout with Varnish' => [true, true, $tags, \Magento\PageCache\Model\Config::VARNISH, 0],
            'Cacheable layout with Varnish, Full_cache state is false' => [
                false,
                true,
                $tags,
                \Magento\PageCache\Model\Config::VARNISH,
                0,
            ],
            'Cacheable layout with Varnish and esi' => [
                true,
                true,
                null,
                \Magento\PageCache\Model\Config::VARNISH,
                100,
            ],
            'Cacheable layout with Builtin' => [true, true, $tags, \Magento\PageCache\Model\Config::BUILT_IN, 0],
            'Cacheable layout with Builtin, Full_cache state is false' => [
                false,
                true,
                $tags,
                \Magento\PageCache\Model\Config::BUILT_IN,
                0,
            ],
            'Cacheable layout with Builtin and esi' => [
                true,
                false,
                $tags,
                \Magento\PageCache\Model\Config::BUILT_IN,
                100,
            ]
        ];
    }

    /**
     * @dataProvider aroundRenderElementDataProvider
     * @param bool $isCacheEnabled
     * @param string $cacheType
     * @param bool $isLayoutCacheable
     * @param int|null $blockTtl
     * @param bool $useBlockCache
     * @param string $expectedOutput
     */
    public function testAroundRenderElement(
        $isCacheEnabled,
        $cacheType,
        $isLayoutCacheable,
        $blockTtl,
        $useBlockCache,
        $expectedOutput
    ) {
        $blockName = 'blockName';
        
        $closure = function ($nameParam, $useCacheParam) {
            return $useCacheParam ? "cached {$nameParam} output" : "non-cached {$nameParam} output";
        };

        $blockMock = $this->getMockForAbstractClass(
            '\Magento\Framework\View\Element\AbstractBlock',
            [],
            '',
            false,
            true,
            true,
            ['getUrl', 'getData']
        );

        $this->configMock->expects($this->any())->method('isEnabled')->will($this->returnValue($isCacheEnabled));
        $this->configMock->expects($this->any())->method('getType')->will($this->returnValue($cacheType));

        if ($isCacheEnabled) {
            $this->layoutMock->expects($this->once())
                ->method('isCacheable')
                ->will($this->returnValue($isLayoutCacheable));

            $this->layoutMock->expects($this->any())
                ->method('getUpdate')
                ->will($this->returnSelf());

            $this->layoutMock->expects($this->any())
                ->method('getHandles')
                ->will($this->returnValue([]));

            $this->layoutMock->expects($this->once())
                ->method('getBlock')
                ->with($blockName)
                ->willReturn($blockMock);

            if ($blockTtl && $cacheType == \Magento\PageCache\Model\Config::VARNISH) {
                $blockMock->expects($this->once())
                    ->method('getData')
                    ->with('ttl')
                    ->will($this->returnValue($blockTtl));
                $blockMock->expects($this->any())
                    ->method('getUrl')
                    ->with('page_cache/block/esi')
                    ->willReturn('page_cache/block/wrapesi/with/handles/and/other/stuff');
            }
        }
        
        $output = $this->model->aroundRenderElement($this->layoutMock, $closure, $blockName, $useBlockCache);
        $this->assertEquals($expectedOutput, $output);
    }

    public function aroundRenderElementDataProvider()
    {
        return [
            'full_page type and Varnish enabled, ttl is set' => [
                true,
                \Magento\PageCache\Model\Config::VARNISH,
                true,
                360,
                true,
                '<esi:include src="page_cache/block/wrapesi/with/handles/and/other/stuff" />',
            ],
            'full_page type and Varnish enabled, ttl is not set, block cache true' => [
                true,
                \Magento\PageCache\Model\Config::VARNISH,
                true,
                null,
                true,
                'cached blockName output',
            ],
            'full_page type disabled and Varnish enabled, ttl is set, block cache false' => [
                false,
                \Magento\PageCache\Model\Config::VARNISH,
                true,
                360,
                false,
                'non-cached blockName output',
            ],
            'full_page type enabled and Varnish disabled, ttl is set, block cache false' => [
                true,
                \Magento\PageCache\Model\Config::BUILT_IN,
                true,
                360,
                false,
                'non-cached blockName output',
            ],
            'full_page type disabled and Varnish disabled, ttl is set, block cache false' => [
                false,
                \Magento\PageCache\Model\Config::BUILT_IN,
                true,
                360,
                false,
                'non-cached blockName output',
            ]
        ];
    }
}
