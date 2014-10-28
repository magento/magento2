<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Framework\View\Asset;

class MinifyServiceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\View\Asset\ConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_config;

    /**
     * @var \Magento\Framework\ObjectManager|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_objectManager;

    /**
     * @var \Magento\Framework\View\Asset\MinifyService
     */
    protected $_model;

    protected function setUp()
    {
        $this->_config = $this->getMock('Magento\Framework\View\Asset\ConfigInterface', array(), array(), '', false);
        $this->_objectManager = $this->getMockForAbstractClass('Magento\Framework\ObjectManager');
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
        $resultOne = $this->getMock('Magento\Framework\View\Asset\Minified', array(), array(), '', false);
        $assetTwo = $this->getMockForAbstractClass('Magento\Framework\View\Asset\LocalInterface');
        $assetTwo->expects($this->once())
            ->method('getContentType')
            ->will($this->returnValue('js'));
        $resultTwo = $this->getMock('Magento\Framework\View\Asset\Minified', array(), array(), '', false);
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
                array(
                    array(
                        'Magento\Framework\View\Asset\Minified',
                        array('asset' => $assetOne, 'strategy' => $expectedStrategy, 'adapter' => $minifier),
                        $resultOne
                    ),
                    array(
                        'Magento\Framework\View\Asset\Minified',
                        array('asset' => $assetTwo, 'strategy' => $expectedStrategy, 'adapter' => $minifier),
                        $resultTwo
                    ),
                )
            ));
        $model = new MinifyService($this->_config, $this->_objectManager, $appMode);
        $result = $model->getAssets(array($assetOne, $assetTwo));
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
        return array(
            'production' => array(
                \Magento\Framework\App\State::MODE_PRODUCTION,
                Minified::FILE_EXISTS
            ),
            'default'    => array(
                \Magento\Framework\App\State::MODE_DEFAULT,
                Minified::MTIME
            ),
            'developer'  => array(
                \Magento\Framework\App\State::MODE_DEVELOPER,
                Minified::MTIME
            ),
        );
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

        $minifiedAssets = $this->_model->getAssets(array($asset));
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

        $this->_model->getAssets(array($asset));
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
        $obj = new \StdClass;
        $this->_objectManager->expects($this->once())->method('get')->with('StdClass')->will($this->returnValue($obj));

        $this->_model->getAssets(array($asset));
    }
}
