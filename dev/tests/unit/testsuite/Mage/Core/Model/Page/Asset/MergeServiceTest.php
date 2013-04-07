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
 * @category    Magento
 * @package     Mage_Core
 * @subpackage  unit_tests
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
    protected $_designPackage;

    /**
     * Whether mock of design package allows merging
     *
     * @bool
     */
    protected $_isMergingAllowed = true;

    public function setUp()
    {
        $this->_objectManager = $this->getMockForAbstractClass('Magento_ObjectManager', array('create'));

        $this->_storeConfig = $this->getMock('Mage_Core_Model_Store_Config', array('getConfigFlag'));

        $this->_isMergingAllowed = true;
        $this->_designPackage = $this->getMock('Mage_Core_Model_Design_Package', array(), array(), '', false);
        $this->_designPackage->expects($this->any())
            ->method('isMergingViewFilesAllowed')
            ->will($this->returnCallback(array($this, 'isMergingAllowed')));

        $this->_object = new Mage_Core_Model_Page_Asset_MergeService($this->_objectManager, $this->_storeConfig,
            $this->_designPackage);
    }

    /**
     * Return whether currently merging is allowed by mock of design package
     *
     * @return bool
     */
    public function isMergingAllowed()
    {
        return $this->_isMergingAllowed;
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
     * @dataProvider getMergedAssets
     */
    public function testGetMergedAssetsMergeDisabled(array $assets, $contentType)
    {
        $this->assertSame($assets, $this->_object->getMergedAssets($assets, $contentType));
    }

    /**
     * @param array $assets
     * @param string $contentType
     * @dataProvider getMergedAssets
     */
    public function testGetMergedAssetsMergeDisabledBySystem(array $assets, $contentType, $storeConfigPath)
    {
        // Make sure we enable the js/css merging
        $this->_storeConfig
            ->expects($this->any())
            ->method('getConfigFlag')
            ->will($this->returnValueMap(array(
            array($storeConfigPath, null, true),
        )))
        ;

        // Disable merging for whole system, which must overwrite settings for js/css
        $this->_isMergingAllowed = false;

        // Test
        $this->assertSame($assets, $this->_object->getMergedAssets($assets, $contentType));
    }

    /**
     * @param array $assets
     * @param string $contentType
     * @param string $storeConfigPath
     * @dataProvider getMergedAssets
     */
    public function testGetMergedAssetsMergeEnabled(array $assets, $contentType, $storeConfigPath)
    {
        $mergedAsset = $this->getMock('Mage_Core_Model_Page_Asset_AssetInterface');
        $this->_storeConfig
            ->expects($this->any())
            ->method('getConfigFlag')
            ->will($this->returnValueMap(array(
                array($storeConfigPath, null, true),
            )))
        ;
        $this->_objectManager
            ->expects($this->once())
            ->method('create')
            ->with('Mage_Core_Model_Page_Asset_Merged', array('assets' => $assets))
            ->will($this->returnValue($mergedAsset))
        ;
        $this->assertSame(array($mergedAsset), $this->_object->getMergedAssets($assets, $contentType));
    }

    public function getMergedAssets()
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
            'js' => array(
                $jsAssets,
                Mage_Core_Model_Design_Package::CONTENT_TYPE_JS,
                Mage_Core_Model_Page_Asset_MergeService::XML_PATH_MERGE_JS_FILES,
            ),
            'css' => array(
                $cssAssets,
                Mage_Core_Model_Design_Package::CONTENT_TYPE_CSS,
                Mage_Core_Model_Page_Asset_MergeService::XML_PATH_MERGE_CSS_FILES,
            ),
        );
    }
}
