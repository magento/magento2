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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Core\Model\Page\Asset;

class MinifyServiceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Core\Model\Store\Config|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_storeConfig;

    /**
     * @var \Magento\ObjectManager|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_objectManager;

    /**
     * @var \Magento\Core\Model\Page\Asset\MinifyService
     */
    protected $_model;

    /**
     * @var \Magento\App\State|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_appState;

    protected function setUp()
    {
        $this->_storeConfig = $this->getMock('Magento\Core\Model\Store\Config', array(), array(), '', false);
        $dirs = $this->getMock('Magento\App\Dir', array(), array(), '', false);
        $this->_objectManager = $this->getMock('Magento\ObjectManager');
        $this->_appState = $this->getMock('Magento\App\State', array(), array(), '', false);

        $this->_model = new \Magento\Core\Model\Page\Asset\MinifyService($this->_storeConfig, $this->_objectManager,
            $dirs, $this->_appState);
    }

    public function testGetAssets()
    {
        $assetOne = $this->getMockForAbstractClass('Magento\Core\Model\Page\Asset\LocalInterface');
        $assetOne->expects($this->once())
            ->method('getContentType')
            ->will($this->returnValue('js'));
        $assetTwo = $this->getMockForAbstractClass('Magento\Core\Model\Page\Asset\LocalInterface');
        $assetTwo->expects($this->once())
            ->method('getContentType')
            ->will($this->returnValue('js'));

        $this->_storeConfig->expects($this->once())
            ->method('getConfigFlag')
            ->with('dev/js/minify_files')
            ->will($this->returnValue(true));
        $this->_storeConfig->expects($this->once())
            ->method('getConfig')
            ->with('dev/js/minify_adapter')
            ->will($this->returnValue('Magento\Code\Minifier\AdapterInterface'));

        $self = $this;
        $this->_objectManager->expects($this->any())
            ->method('create')
            ->will($this->returnCallback(
                function ($className) use ($self) {
                    return $self->getMock($className, array(), array(), '', false);
                }
            ));

        $minifiedAssets = $this->_model->getAssets(array($assetOne, $assetTwo));
        $this->assertCount(2, $minifiedAssets);
        $this->assertNotSame($minifiedAssets[0], $minifiedAssets[1]);
        $this->assertInstanceOf('Magento\Core\Model\Page\Asset\Minified', $minifiedAssets[0]);
        $this->assertInstanceOf('Magento\Core\Model\Page\Asset\Minified', $minifiedAssets[1]);
    }

    public function testGetAssetsDisabled()
    {
        $asset = $this->getMockForAbstractClass('Magento\Core\Model\Page\Asset\LocalInterface');
        $asset->expects($this->once())
            ->method('getContentType')
            ->will($this->returnValue('js'));

        $this->_storeConfig->expects($this->once())
            ->method('getConfigFlag')
            ->with('dev/js/minify_files')
            ->will($this->returnValue(false));
        $this->_storeConfig->expects($this->never())
            ->method('getConfig');

        $minifiedAssets = $this->_model->getAssets(array($asset));
        $this->assertCount(1, $minifiedAssets);
        $this->assertSame($asset, $minifiedAssets[0]);
    }

    /**
     * @expectedException \Magento\Core\Exception
     * @expectedExceptionMessage Minification adapter is not specified for 'js' content type
     */
    public function testGetAssetsNoAdapterDefined()
    {
        $this->_storeConfig->expects($this->once())
            ->method('getConfigFlag')
            ->with('dev/js/minify_files')
            ->will($this->returnValue(true));
        $asset = $this->getMockForAbstractClass('Magento\Core\Model\Page\Asset\LocalInterface');
        $asset->expects($this->once())
            ->method('getContentType')
            ->will($this->returnValue('js'));
        $this->_model->getAssets(array($asset));
    }

    /**
     * @param string $mode
     * @param string $expectedStrategy
     * @dataProvider getAssetsAppModesDataProvider
     */
    public function testGetAssetsAppModes($mode, $expectedStrategy)
    {
        $this->_appState->expects($this->once())
            ->method('getMode')
            ->will($this->returnValue($mode));

        $asset = $this->getMockForAbstractClass('Magento\Core\Model\Page\Asset\LocalInterface');
        $asset->expects($this->once())
            ->method('getContentType')
            ->will($this->returnValue('js'));

        $this->_storeConfig->expects($this->once())
            ->method('getConfigFlag')
            ->with('dev/js/minify_files')
            ->will($this->returnValue(true));
        $this->_storeConfig->expects($this->once())
            ->method('getConfig')
            ->with('dev/js/minify_adapter')
            ->will($this->returnValue('Magento\Code\Minifier\AdapterInterface'));

        $this->_objectManager->expects($this->at(1))
            ->method('create')
            ->with($expectedStrategy);

        $this->_model->getAssets(array($asset));
    }

    /**
     * @return array
     */
    public function getAssetsAppModesDataProvider()
    {
        return array(
            'production' => array(
                \Magento\App\State::MODE_PRODUCTION,
                'Magento\Code\Minifier\Strategy\Lite'
            ),
            'default'    => array(
                \Magento\App\State::MODE_DEFAULT,
                'Magento\Code\Minifier\Strategy\Generate'
            ),
            'developer'  => array(
                \Magento\App\State::MODE_DEVELOPER,
                'Magento\Code\Minifier\Strategy\Generate'
            ),
        );
    }
}
