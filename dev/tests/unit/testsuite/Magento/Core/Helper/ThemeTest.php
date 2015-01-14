<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Core\Helper;

class ThemeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider getCssAssetsDataProvider
     * @param string $layoutStr
     * @param array $expectedResult
     */
    public function testGetCssAssets($layoutStr, $expectedResult)
    {
        $theme = $this->getMockForAbstractClass('\Magento\Framework\View\Design\ThemeInterface');
        $theme->expects($this->once())->method('getArea')->will($this->returnValue('area'));
        $layoutMergeFactory = $this->_getLayoutMergeFactory($theme, $layoutStr);
        $assetRepo = $this->getMock(
            'Magento\Framework\View\Asset\Repository', ['createAsset'], [], '', false
        );
        $assetRepo->expects($this->any())
            ->method('createAsset')
            ->will($this->returnArgument(0));
        $helper = new \Magento\Core\Helper\Theme(
            $this->getMock('Magento\Framework\App\Helper\Context', [], [], '', false),
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
    public function getCssAssetsDataProvider()
    {
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
                            <argument name="file" xsi:type="string">Magento_Core::test3.css</argument>
                        </arguments>
                    </block>
                </block>',
                ['Magento_Core::test3.css' => 'Magento_Core::test3.css'],
            ],
            [
                '<block class="Magento\Theme\Block\Html\Head" name="head">
                    <block class="Magento\Theme\Block\Html\Head\Css" name="magento-loader-js">
                        <arguments><argument name="file" xsi:type="string">test.css</argument></arguments>
                    </block>
                    <block class="Magento\Theme\Block\Html\Head\Css" name="magento-loader-js">
                        <arguments>
                            <argument name="file" xsi:type="string">Magento_Core::test.css</argument>
                        </arguments>
                    </block>
                </block>
                <referenceBlock name="head">
                    <block class="Magento\Theme\Block\Html\Head\Css" name="magento-loader-js">
                        <arguments><argument name="file" xsi:type="string">testh.css</argument></arguments>
                    </block>
                    <block class="Magento\Theme\Block\Html\Head\Css" name="magento-loader-js">
                        <arguments><argument name="file" xsi:type="string">Magento_Core::test.css</argument></arguments>
                    </block>
                </referenceBlock>
                <block type="Some_Block_Class">
                    <block class="Magento\Theme\Block\Html\Head\Css" name="magento-loader-js">
                        <arguments><argument name="file" xsi:type="string">testa.css</argument></arguments>
                    </block>
                    <block class="Magento\Theme\Block\Html\Head\Css" name="magento-loader-js">
                        <arguments>
                            <argument name="file" xsi:type="string">Magento_Core::testa.css</argument>
                        </arguments>
                    </block>
                </block>
                <referenceBlock name="some_block_name">
                    <block class="Magento\Theme\Block\Html\Head\Css" name="magento-loader-js">
                        <arguments><argument name="file" xsi:type="string">testb.css</argument></arguments>
                    </block>
                    <block class="Magento\Theme\Block\Html\Head\Css" name="magento-loader-js">
                        <arguments>
                            <argument name="file" xsi:type="string">Magento_Core::testb.css</argument>
                        </arguments>
                    </block>
                </referenceBlock>',
                [
                    'Magento_Core::test.css' => 'Magento_Core::test.css',
                    'test.css' => 'test.css',
                    'testh.css' => 'testh.css',
                ],
            ],
        ];
    }

    /**
     * @param \PHPUnit_Framework_MockObject_MockObject $theme
     * @param string $layoutStr
     * @return \Magento\Framework\View\Layout\ProcessorFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function _getLayoutMergeFactory($theme, $layoutStr)
    {
        /** @var $layoutProcessor \Magento\Framework\View\Layout\ProcessorInterface */
        $layoutProcessor = $this->getMockBuilder('Magento\Framework\View\Layout\ProcessorInterface')
            ->getMockForAbstractClass();
        $xml = '<layouts xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">' . $layoutStr . '</layouts>';
        $layoutElement = simplexml_load_string($xml);
        $layoutProcessor->expects(
            $this->any()
        )->method(
            'getFileLayoutUpdatesXml'
        )->will(
            $this->returnValue($layoutElement)
        );

        /** @var $processorFactory \Magento\Framework\View\Layout\ProcessorFactory */
        $processorFactory = $this->getMock(
            'Magento\Framework\View\Layout\ProcessorFactory', ['create'], [], '', false
        );
        $processorFactory->expects($this->any())
            ->method('create')
            ->with(['theme' => $theme])
            ->will($this->returnValue($layoutProcessor));

        return $processorFactory;
    }
}
