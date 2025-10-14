<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Theme\Test\Unit\Helper;

use Magento\Framework\App\Helper\Context;
use Magento\Framework\View\Asset\Repository;
use Magento\Framework\View\Design\ThemeInterface;
use Magento\Framework\View\Layout\ProcessorFactory;
use Magento\Framework\View\Layout\ProcessorInterface;
use Magento\Theme\Helper\Theme;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ThemeTest extends TestCase
{
    /**
     * @dataProvider getCssAssetsDataProvider
     * @param string $layoutStr
     * @param array $expectedResult
     */
    public function testGetCssAssets($layoutStr, $expectedResult)
    {
        $theme = $this->getMockForAbstractClass(ThemeInterface::class);
        $theme->expects($this->once())->method('getArea')->willReturn('area');
        $layoutMergeFactory = $this->_getLayoutMergeFactory($theme, $layoutStr);
        $assetRepo = $this->createPartialMock(Repository::class, ['createAsset']);
        $assetRepo->expects($this->any())
            ->method('createAsset')
            ->willReturnArgument(0);
        $helper = new Theme(
            $this->createMock(Context::class),
            $layoutMergeFactory,
            $assetRepo
        );
        $result = $helper->getCssAssets($theme);
        $this->assertSame($expectedResult, $result);
    }

    /**
     * @return array
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public static function getCssAssetsDataProvider()
    {
        // @codingStandardsIgnoreStart
        return [
            [
                '<block class="Magento\Theme\Block\Html\Head" name="head">
                    <block class="Magento\Theme\Block\Html\Head\Css" name="magento-loader-js">
                        <arguments><argument name="file" xsi:type="string">test1.css</argument></arguments>
                    </block>
                </block>',
                ['test1.css' => 'test1.css'],
            ],
            [
                '<block class="Magento\Theme\Block\Html\Head" name="head">
                    <block class="Magento\Theme\Block\Html\Head\Css" name="magento-loader-js">
                        <arguments>
                            <argument name="file" xsi:type="string">Magento_Theme::test3.css</argument>
                        </arguments>
                    </block>
                </block>',
                ['Magento_Theme::test3.css' => 'Magento_Theme::test3.css'],
            ],
            [
                '<block class="Magento\Theme\Block\Html\Head" name="head">
                    <block class="Magento\Theme\Block\Html\Head\Css" name="magento-loader-js">
                        <arguments><argument name="file" xsi:type="string">test.css</argument></arguments>
                    </block>
                    <block class="Magento\Theme\Block\Html\Head\Css" name="magento-loader-js">
                        <arguments>
                            <argument name="file" xsi:type="string">Magento_Theme::test.css</argument>
                        </arguments>
                    </block>
                </block>
                <referenceBlock name="head">
                    <block class="Magento\Theme\Block\Html\Head\Css" name="magento-loader-js">
                        <arguments><argument name="file" xsi:type="string">testh.css</argument></arguments>
                    </block>
                    <block class="Magento\Theme\Block\Html\Head\Css" name="magento-loader-js">
                        <arguments><argument name="file" xsi:type="string">Magento_Theme::test.css</argument></arguments>
                    </block>
                </referenceBlock>
                <block type="Some_Block_Class">
                    <block class="Magento\Theme\Block\Html\Head\Css" name="magento-loader-js">
                        <arguments><argument name="file" xsi:type="string">testa.css</argument></arguments>
                    </block>
                    <block class="Magento\Theme\Block\Html\Head\Css" name="magento-loader-js">
                        <arguments>
                            <argument name="file" xsi:type="string">Magento_Theme::testa.css</argument>
                        </arguments>
                    </block>
                </block>
                <referenceBlock name="some_block_name">
                    <block class="Magento\Theme\Block\Html\Head\Css" name="magento-loader-js">
                        <arguments><argument name="file" xsi:type="string">testb.css</argument></arguments>
                    </block>
                    <block class="Magento\Theme\Block\Html\Head\Css" name="magento-loader-js">
                        <arguments>
                            <argument name="file" xsi:type="string">Magento_Theme::testb.css</argument>
                        </arguments>
                    </block>
                </referenceBlock>',
                [
                    'Magento_Theme::test.css' => 'Magento_Theme::test.css',
                    'test.css' => 'test.css',
                    'testh.css' => 'testh.css',
                ],
            ],
        ];
        // @codingStandardsIgnoreEnd
    }

    /**
     * @param MockObject $theme
     * @param string $layoutStr
     * @return ProcessorFactory|MockObject
     */
    protected function _getLayoutMergeFactory($theme, $layoutStr)
    {
        /** @var $layoutProcessor \Magento\Framework\View\Layout\ProcessorInterface */
        $layoutProcessor = $this->getMockBuilder(ProcessorInterface::class)
            ->getMockForAbstractClass();
        $xml = '<layouts xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">' . $layoutStr . '</layouts>';
        $layoutElement = simplexml_load_string($xml);
        $layoutProcessor->expects(
            $this->any()
        )->method(
            'getFileLayoutUpdatesXml'
        )->willReturn(
            $layoutElement
        );

        /** @var $processorFactory \Magento\Framework\View\Layout\ProcessorFactory */
        $processorFactory = $this->createPartialMock(
            ProcessorFactory::class,
            ['create']
        );
        $processorFactory->expects($this->any())
            ->method('create')
            ->with(['theme' => $theme])
            ->willReturn($layoutProcessor);

        return $processorFactory;
    }
}
