<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Asset;

class MinifyServiceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\View\Asset\ConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_config;

    /**
     * @var \Magento\Framework\ObjectManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_objectManager;

    /**
     * @var \Magento\Framework\View\Asset\MinifyService
     */
    protected $_model;

    protected function setUp()
    {
        $this->_config = $this->getMock('Magento\Framework\View\Asset\ConfigInterface', [], [], '', false);
        $this->_objectManager = $this->getMock('Magento\Framework\ObjectManagerInterface');
        $this->_model = new MinifyService($this->_config, $this->_objectManager);
    }

    /**
     * @param $appMode
     * @param $expectedStrategy
     * @dataProvider getAssetsDataProvider
     */
    public function testGetAssets($appMode, $expectedStrategy)
    {
        $assetOne = $this->getMockForAbstractClass('Magento\Framework\View\Asset\LocalInterface');
        $assetOne->expects($this->once())
            ->method('getContentType')
            ->will($this->returnValue('js'));
        $resultOne = $this->getMock('Magento\Framework\View\Asset\Minified', [], [], '', false);
        $assetTwo = $this->getMockForAbstractClass('Magento\Framework\View\Asset\LocalInterface');
        $assetTwo->expects($this->once())
            ->method('getContentType')
            ->will($this->returnValue('js'));
        $resultTwo = $this->getMock('Magento\Framework\View\Asset\Minified', [], [], '', false);
        $this->_config->expects($this->once())
            ->method('isAssetMinification')
            ->with('js')
            ->will($this->returnValue(true));
        $minifier = $this->getMockForAbstractClass('Magento\Framework\Code\Minifier\AdapterInterface');
        $this->_config->expects($this->once())
            ->method('getAssetMinificationAdapter')
            ->with('js')
            ->will($this->returnValue('Magento\Framework\Code\Minifier\AdapterInterface'));
        $this->_objectManager->expects($this->once())
            ->method('get')
            ->with('Magento\Framework\Code\Minifier\AdapterInterface')
            ->will($this->returnValue($minifier));
        $this->_objectManager->expects($this->exactly(2))
            ->method('create')
            ->will($this->returnValueMap(
                [
                    [
                        'Magento\Framework\View\Asset\Minified',
                        ['asset' => $assetOne, 'strategy' => $expectedStrategy, 'adapter' => $minifier],
                        $resultOne,
                    ],
                    [
                        'Magento\Framework\View\Asset\Minified',
                        ['asset' => $assetTwo, 'strategy' => $expectedStrategy, 'adapter' => $minifier],
                        $resultTwo
                    ],
                ]
            ));
        $model = new MinifyService($this->_config, $this->_objectManager, $appMode);
        $result = $model->getAssets([$assetOne, $assetTwo]);
        $this->assertArrayHasKey(0, $result);
        $this->assertSame($resultOne, $result[0]);
        $this->assertArrayHasKey(1, $result);
        $this->assertSame($resultTwo, $result[1]);
    }

    /**
     * @return array
     */
    public function getAssetsDataProvider()
    {
        return [
            'production' => [
                \Magento\Framework\App\State::MODE_PRODUCTION,
                Minified::FILE_EXISTS,
            ],
            'default'    => [
                \Magento\Framework\App\State::MODE_DEFAULT,
                Minified::MTIME,
            ],
            'developer'  => [
                \Magento\Framework\App\State::MODE_DEVELOPER,
                Minified::MTIME,
            ],
        ];
    }

    public function testGetAssetsDisabled()
    {
        $asset = $this->getMockForAbstractClass('Magento\Framework\View\Asset\LocalInterface');
        $asset->expects($this->once())
            ->method('getContentType')
            ->will($this->returnValue('js'));

        $this->_config->expects($this->once())
            ->method('isAssetMinification')
            ->with('js')
            ->will($this->returnValue(false));
        $this->_config->expects($this->never())
            ->method('getAssetMinificationAdapter');

        $minifiedAssets = $this->_model->getAssets([$asset]);
        $this->assertCount(1, $minifiedAssets);
        $this->assertSame($asset, $minifiedAssets[0]);
    }

    /**
     * @expectedException \Magento\Framework\Exception
     * @expectedExceptionMessage Minification adapter is not specified for 'js' content type
     */
    public function testGetAssetsNoAdapterDefined()
    {
        $asset = $this->getMockForAbstractClass('Magento\Framework\View\Asset\LocalInterface');
        $asset->expects($this->once())
            ->method('getContentType')
            ->will($this->returnValue('js'));

        $this->_config->expects($this->once())
            ->method('isAssetMinification')
            ->with('js')
            ->will($this->returnValue(true));
        $this->_config->expects($this->once())
            ->method('getAssetMinificationAdapter')
            ->with('js');

        $this->_model->getAssets([$asset]);
    }

    public function testGetAssetsInvalidAdapter()
    {
        $this->setExpectedException(
            '\Magento\Framework\Exception',
            'Invalid adapter: \'stdClass\'. Expected: \Magento\Framework\Code\Minifier\AdapterInterface'
        );
        $asset = $this->getMockForAbstractClass('Magento\Framework\View\Asset\LocalInterface');
        $asset->expects($this->once())
            ->method('getContentType')
            ->will($this->returnValue('js'));
        $this->_config->expects($this->once())
            ->method('isAssetMinification')
            ->with('js')
            ->will($this->returnValue(true));
        $this->_config->expects($this->once())
            ->method('getAssetMinificationAdapter')
            ->with('js')
            ->will($this->returnValue('StdClass'));
        $obj = new \StdClass();
        $this->_objectManager->expects($this->once())->method('get')->with('StdClass')->will($this->returnValue($obj));

        $this->_model->getAssets([$asset]);
    }
}
