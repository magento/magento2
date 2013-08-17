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

class Mage_Core_Model_Page_Asset_MergeServiceTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Mage_Core_Model_Page_Asset_MergeService
     */
    protected $_object;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $_objectManager;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $_storeConfig;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $_filesystem;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $_dirs;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $_state;

    public function setUp()
    {
        $this->_objectManager = $this->getMockForAbstractClass('Magento_ObjectManager', array('create'));
        $this->_storeConfig = $this->getMock('Mage_Core_Model_Store_Config', array('getConfigFlag'));
        $this->_filesystem = $this->getMock('Magento_Filesystem', array(), array(), '', false);
        $this->_dirs = $this->getMock('Mage_Core_Model_Dir', array(), array(), '', false);
        $this->_state = $this->getMock('Mage_Core_Model_App_State', array(), array(), '', false);

        $this->_object = new Mage_Core_Model_Page_Asset_MergeService(
            $this->_objectManager,
            $this->_storeConfig,
            $this->_filesystem,
            $this->_dirs,
            $this->_state
        );
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Merge for content type 'unknown' is not supported.
     */
    public function testGetMergedAssetsWrongContentType()
    {
        $this->_object->getMergedAssets(array(), 'unknown');
    }

    /**
     * @param array $assets
     * @param string $contentType
     * @param string $storeConfigPath
     * @param string $appMode
     * @param string $mergeStrategy
     * @dataProvider getMergedAssetsDataProvider
     */
    public function testGetMergedAssets(array $assets, $contentType, $storeConfigPath, $appMode, $mergeStrategy)
    {
        $mergedAsset = $this->getMock('Mage_Core_Model_Page_Asset_AssetInterface');
        $this->_storeConfig
            ->expects($this->any())
            ->method('getConfigFlag')
            ->will($this->returnValueMap(array(
                array($storeConfigPath, null, true),
            )))
        ;

        $mergeStrategyMock = $this->getMock($mergeStrategy, array(), array(), '', false);

        $this->_objectManager
            ->expects($this->once())
            ->method('create')
            ->with(
                'Mage_Core_Model_Page_Asset_Merged', array('assets' => $assets, 'mergeStrategy' => $mergeStrategyMock)
            )
            ->will($this->returnValue($mergedAsset))
        ;

        $this->_objectManager
            ->expects($this->once())
            ->method('get')
            ->with($mergeStrategy)
            ->will($this->returnValue($mergeStrategyMock))
        ;
        $this->_state
            ->expects($this->once())
            ->method('getMode')
            ->will($this->returnValue($appMode));
        $this->assertSame($mergedAsset, $this->_object->getMergedAssets($assets, $contentType));
    }

    public static function getMergedAssetsDataProvider()
    {
        $jsAssets = array(
            new Mage_Core_Model_Page_Asset_Remote('http://127.0.0.1/magento/script_one.js'),
            new Mage_Core_Model_Page_Asset_Remote('http://127.0.0.1/magento/script_two.js')
        );
        $cssAssets = array(
            new Mage_Core_Model_Page_Asset_Remote('http://127.0.0.1/magento/style_one.css'),
            new Mage_Core_Model_Page_Asset_Remote('http://127.0.0.1/magento/style_two.css')
        );
        return array(
            'js production mode' => array(
                $jsAssets,
                Mage_Core_Model_View_Publisher::CONTENT_TYPE_JS,
                Mage_Core_Model_Page_Asset_MergeService::XML_PATH_MERGE_JS_FILES,
                Mage_Core_Model_App_State::MODE_PRODUCTION,
                'Mage_Core_Model_Page_Asset_MergeStrategy_FileExists'
            ),
            'css production mode' => array(
                $cssAssets,
                Mage_Core_Model_View_Publisher::CONTENT_TYPE_CSS,
                Mage_Core_Model_Page_Asset_MergeService::XML_PATH_MERGE_CSS_FILES,
                Mage_Core_Model_App_State::MODE_PRODUCTION,
                'Mage_Core_Model_Page_Asset_MergeStrategy_FileExists'
            ),
            'js default mode' => array(
                $jsAssets,
                Mage_Core_Model_View_Publisher::CONTENT_TYPE_JS,
                Mage_Core_Model_Page_Asset_MergeService::XML_PATH_MERGE_JS_FILES,
                Mage_Core_Model_App_State::MODE_DEFAULT,
                'Mage_Core_Model_Page_Asset_MergeStrategy_Checksum'
            ),
            'css default mode' => array(
                $cssAssets,
                Mage_Core_Model_View_Publisher::CONTENT_TYPE_CSS,
                Mage_Core_Model_Page_Asset_MergeService::XML_PATH_MERGE_CSS_FILES,
                Mage_Core_Model_App_State::MODE_DEFAULT,
                'Mage_Core_Model_Page_Asset_MergeStrategy_Checksum'
            ),
            'js developer mode' => array(
                $jsAssets,
                Mage_Core_Model_View_Publisher::CONTENT_TYPE_JS,
                Mage_Core_Model_Page_Asset_MergeService::XML_PATH_MERGE_JS_FILES,
                Mage_Core_Model_App_State::MODE_DEVELOPER,
                'Mage_Core_Model_Page_Asset_MergeStrategy_Checksum'
            ),
            'css developer mode' => array(
                $cssAssets,
                Mage_Core_Model_View_Publisher::CONTENT_TYPE_CSS,
                Mage_Core_Model_Page_Asset_MergeService::XML_PATH_MERGE_CSS_FILES,
                Mage_Core_Model_App_State::MODE_DEVELOPER,
                'Mage_Core_Model_Page_Asset_MergeStrategy_Checksum'
            ),
        );
    }

    public function testCleanMergedJsCss()
    {
        $this->_dirs->expects($this->once())
            ->method('getDir')
            ->with(Mage_Core_Model_Dir::PUB_VIEW_CACHE)
            ->will($this->returnValue('/pub/cache'));

        $mergedDir = '/pub/cache/' . Mage_Core_Model_Page_Asset_Merged::PUBLIC_MERGE_DIR;
        $this->_filesystem->expects($this->once())
            ->method('delete')
            ->with($mergedDir, null);

        $mediaStub = $this->getMock('Mage_Core_Helper_File_Storage_Database', array(), array(), '', false);
        $mediaStub->expects($this->once())
            ->method('deleteFolder')
            ->with($mergedDir);
        $this->_objectManager->expects($this->once())
            ->method('get')
            ->with('Mage_Core_Helper_File_Storage_Database')
            ->will($this->returnValue($mediaStub));

        $this->_object->cleanMergedJsCss();
    }
}
