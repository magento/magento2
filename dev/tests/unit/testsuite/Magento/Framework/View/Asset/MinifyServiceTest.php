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

    /**
     * @var \Magento\Framework\App\State|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_appState;

    protected function setUp()
    {
        $this->_config = $this->getMock('Magento\Framework\View\Asset\ConfigInterface', array(), array(), '', false);
        $this->_objectManager = $this->getMock('Magento\Framework\ObjectManager');
        $this->_appState = $this->getMock('Magento\Framework\App\State', array(), array(), '', false);
        $filesystem = $this->getMock('Magento\Framework\App\Filesystem', array(), array(), '', false);
        $directory = $this->getMock('Magento\Framework\Filesystem\Directory\Read', array(), array(), '', false);
        $filesystem->expects($this->any())->method('getDirectoryRead')->will($this->returnValue($directory));
        $directory->expects($this->any())->method('getAbsolutePath')->will($this->returnArgument(0));
        $this->_model = new \Magento\Framework\View\Asset\MinifyService(
            $this->_config,
            $this->_objectManager,
            $this->_appState,
            $filesystem
        );
    }

    public function testGetAssets()
    {
        $assetOne = $this->getMockForAbstractClass('Magento\Framework\View\Asset\LocalInterface');
        $assetOne->expects($this->once())->method('getContentType')->will($this->returnValue('js'));
        $assetTwo = $this->getMockForAbstractClass('Magento\Framework\View\Asset\LocalInterface');
        $assetTwo->expects($this->once())->method('getContentType')->will($this->returnValue('js'));

        $this->_config->expects(
            $this->once()
        )->method(
            'isAssetMinification'
        )->with(
            'js'
        )->will(
            $this->returnValue(true)
        );
        $this->_config->expects(
            $this->once()
        )->method(
            'getAssetMinificationAdapter'
        )->with(
            'js'
        )->will(
            $this->returnValue('Magento\Framework\Code\Minifier\AdapterInterface')
        );

        $self = $this;
        $this->_objectManager->expects($this->any())->method('create')->will(
            $this->returnCallback(
                function ($className) use ($self) {
                    return $self->getMock($className, array(), array(), '', false);
                }
            )
        );

        $minifiedAssets = $this->_model->getAssets(array($assetOne, $assetTwo));
        $this->assertCount(2, $minifiedAssets);
        $this->assertNotSame($minifiedAssets[0], $minifiedAssets[1]);
        $this->assertInstanceOf('Magento\Framework\View\Asset\Minified', $minifiedAssets[0]);
        $this->assertInstanceOf('Magento\Framework\View\Asset\Minified', $minifiedAssets[1]);
    }

    public function testGetAssetsDisabled()
    {
        $asset = $this->getMockForAbstractClass('Magento\Framework\View\Asset\LocalInterface');
        $asset->expects($this->once())->method('getContentType')->will($this->returnValue('js'));

        $this->_config->expects(
            $this->once()
        )->method(
            'isAssetMinification'
        )->with(
            'js'
        )->will(
            $this->returnValue(false)
        );
        $this->_config->expects($this->never())->method('getAssetMinificationAdapter');

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
        $asset->expects($this->once())->method('getContentType')->will($this->returnValue('js'));

        $this->_config->expects(
            $this->once()
        )->method(
            'isAssetMinification'
        )->with(
            'js'
        )->will(
            $this->returnValue(true)
        );
        $this->_config->expects($this->once())->method('getAssetMinificationAdapter')->with('js');

        $this->_model->getAssets(array($asset));
    }

    /**
     * @param string $mode
     * @param string $expectedStrategy
     * @dataProvider getAssetsAppModesDataProvider
     */
    public function testGetAssetsAppModes($mode, $expectedStrategy)
    {
        $this->_appState->expects($this->once())->method('getMode')->will($this->returnValue($mode));

        $asset = $this->getMockForAbstractClass('Magento\Framework\View\Asset\LocalInterface');
        $asset->expects($this->once())->method('getContentType')->will($this->returnValue('js'));

        $this->_config->expects(
            $this->once()
        )->method(
            'isAssetMinification'
        )->with(
            'js'
        )->will(
            $this->returnValue(true)
        );
        $this->_config->expects(
            $this->once()
        )->method(
            'getAssetMinificationAdapter'
        )->with(
            'js'
        )->will(
            $this->returnValue('Magento\Framework\Code\Minifier\AdapterInterface')
        );

        $this->_objectManager->expects($this->at(1))->method('create')->with($expectedStrategy);

        $this->_model->getAssets(array($asset));
    }

    /**
     * @return array
     */
    public function getAssetsAppModesDataProvider()
    {
        return array(
            'production' => array(
                \Magento\Framework\App\State::MODE_PRODUCTION,
                'Magento\Framework\Code\Minifier\Strategy\Lite'
            ),
            'default' => array(
                \Magento\Framework\App\State::MODE_DEFAULT,
                'Magento\Framework\Code\Minifier\Strategy\Generate'
            ),
            'developer' => array(
                \Magento\Framework\App\State::MODE_DEVELOPER,
                'Magento\Framework\Code\Minifier\Strategy\Generate'
            )
        );
    }
}
