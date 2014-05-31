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

class MergeServiceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\View\Asset\MergeService
     */
    protected $_object;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_objectManager;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_config;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_filesystem;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_directory;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_state;

    protected function setUp()
    {
        $this->_objectManager = $this->getMockForAbstractClass('Magento\Framework\ObjectManager', array('create'));
        $this->_config = $this->getMock('Magento\Framework\View\Asset\ConfigInterface', array(), array(), '', false);
        $this->_filesystem = $this->getMock('Magento\Framework\App\Filesystem', array(), array(), '', false);
        $this->_directory = $this->getMock(
            '\Magento\Framework\Filesystem\Directory\Write',
            array(),
            array(),
            '',
            false
        );
        $this->_state = $this->getMock('Magento\Framework\App\State', array(), array(), '', false);
        $this->_filesystem->expects(
            $this->any()
        )->method(
            'getDirectoryWrite'
        )->will(
            $this->returnValue($this->_directory)
        );

        $this->_object = new \Magento\Framework\View\Asset\MergeService(
            $this->_objectManager,
            $this->_config,
            $this->_filesystem,
            $this->_state
        );
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Merge for content type 'unknown' is not supported.
     */
    public function testGetMergedAssetsWrongContentType()
    {
        $this->_object->getMergedAssets(array(), 'unknown');
    }

    /**
     * @param array $assets
     * @param string $contentType
     * @param string $appMode
     * @param string $mergeStrategy
     * @dataProvider getMergedAssetsDataProvider
     */
    public function testGetMergedAssets(array $assets, $contentType, $appMode, $mergeStrategy)
    {
        $mergedAsset = $this->getMock('Magento\Framework\View\Asset\AssetInterface');
        $this->_config->expects($this->once())->method('isMergeCssFiles')->will($this->returnValue(true));
        $this->_config->expects($this->once())->method('isMergeJsFiles')->will($this->returnValue(true));

        $mergeStrategyMock = $this->getMock($mergeStrategy, array(), array(), '', false);

        $this->_objectManager->expects(
            $this->once()
        )->method(
            'create'
        )->with(
            'Magento\Framework\View\Asset\Merged',
            array('assets' => $assets, 'mergeStrategy' => $mergeStrategyMock)
        )->will(
            $this->returnValue($mergedAsset)
        );

        $this->_objectManager->expects(
            $this->once()
        )->method(
            'get'
        )->with(
            $mergeStrategy
        )->will(
            $this->returnValue($mergeStrategyMock)
        );
        $this->_state->expects($this->once())->method('getMode')->will($this->returnValue($appMode));
        $this->assertSame($mergedAsset, $this->_object->getMergedAssets($assets, $contentType));
    }

    public static function getMergedAssetsDataProvider()
    {
        $jsAssets = array(
            new \Magento\Framework\View\Asset\Remote('http://127.0.0.1/magento/script_one.js'),
            new \Magento\Framework\View\Asset\Remote('http://127.0.0.1/magento/script_two.js')
        );
        $cssAssets = array(
            new \Magento\Framework\View\Asset\Remote('http://127.0.0.1/magento/style_one.css'),
            new \Magento\Framework\View\Asset\Remote('http://127.0.0.1/magento/style_two.css')
        );
        return array(
            'js production mode' => array(
                $jsAssets,
                'js',
                \Magento\Framework\App\State::MODE_PRODUCTION,
                'Magento\Framework\View\Asset\MergeStrategy\FileExists'
            ),
            'css production mode' => array(
                $cssAssets,
                'css',
                \Magento\Framework\App\State::MODE_PRODUCTION,
                'Magento\Framework\View\Asset\MergeStrategy\FileExists'
            ),
            'js default mode' => array(
                $jsAssets,
                'js',
                \Magento\Framework\App\State::MODE_DEFAULT,
                'Magento\Framework\View\Asset\MergeStrategy\Checksum'
            ),
            'css default mode' => array(
                $cssAssets,
                'js',
                \Magento\Framework\App\State::MODE_DEFAULT,
                'Magento\Framework\View\Asset\MergeStrategy\Checksum'
            ),
            'js developer mode' => array(
                $jsAssets,
                'js',
                \Magento\Framework\App\State::MODE_DEVELOPER,
                'Magento\Framework\View\Asset\MergeStrategy\Checksum'
            ),
            'css developer mode' => array(
                $cssAssets,
                'css',
                \Magento\Framework\App\State::MODE_DEVELOPER,
                'Magento\Framework\View\Asset\MergeStrategy\Checksum'
            )
        );
    }

    public function testCleanMergedJsCss()
    {
        $mergedDir = \Magento\Framework\View\Asset\Merged::getRelativeDir();
        $this->_directory->expects($this->once())->method('delete')->with($mergedDir);

        $this->_object->cleanMergedJsCss();
    }
}
