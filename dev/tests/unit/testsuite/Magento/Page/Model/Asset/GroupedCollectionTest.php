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
 * @package     Magento_Page
 * @subpackage  unit_tests
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Page\Model\Asset;

class GroupedCollectionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Page\Model\Asset\GroupedCollection
     */
    protected $_object;

    /**
     * @var \Magento\Core\Model\Page\Asset\AssetInterface
     */
    protected $_asset;

    protected function setUp()
    {
        $objectManager = $this->getMock('Magento\ObjectManager');
        $objectManager
            ->expects($this->any())
            ->method('create')
            ->with('Magento\Page\Model\Asset\PropertyGroup')
            ->will($this->returnCallback(array($this, 'createAssetGroup')))
        ;
        $this->_object = new \Magento\Page\Model\Asset\GroupedCollection($objectManager);
        $this->_asset = new \Magento\Core\Model\Page\Asset\Remote('http://127.0.0.1/magento/test.css');
        $this->_object->add('asset', $this->_asset);
    }

    protected function tearDown()
    {
        $this->_object = null;
        $this->_asset = null;
    }

    /**
     * Return newly created asset group. Used as a stub for object manger's creation operation.
     *
     * @param string $class
     * @param array $arguments
     * @return \Magento\Page\Model\Asset\PropertyGroup
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function createAssetGroup($class, array $arguments)
    {
        return new \Magento\Page\Model\Asset\PropertyGroup($arguments['properties']);
    }

    /**
     * Assert that actual asset groups equal to expected ones
     *
     * @param array $expectedGroups
     * @param array $actualGroupObjects
     */
    protected function _assertGroups(array $expectedGroups, array $actualGroupObjects)
    {
        $this->assertInternalType('array', $actualGroupObjects);
        $actualGroups = array();
        /** @var $actualGroup \Magento\Page\Model\Asset\PropertyGroup */
        foreach ($actualGroupObjects as $actualGroup) {
            $this->assertInstanceOf('Magento\Page\Model\Asset\PropertyGroup', $actualGroup);
            $actualGroups[] = array(
                'properties' => $actualGroup->getProperties(),
                'assets' => $actualGroup->getAll(),
            );
        }
        $this->assertEquals($expectedGroups, $actualGroups);
    }

    public function testAdd()
    {
        $assetNew = new \Magento\Core\Model\Page\Asset\Remote('http://127.0.0.1/magento/test_new.css');
        $this->_object->add('asset_new', $assetNew, array('test_property' => 'test_value'));
        $this->assertEquals(array('asset' => $this->_asset, 'asset_new' => $assetNew), $this->_object->getAll());
    }

    public function testRemove()
    {
        $this->_object->remove('asset');
        $this->assertEquals(array(), $this->_object->getAll());
    }

    public function testGetGroups()
    {
        $cssAsset = new \Magento\Core\Model\Page\Asset\Remote('http://127.0.0.1/style.css', 'css');
        $jsAsset = new \Magento\Core\Model\Page\Asset\Remote('http://127.0.0.1/script.js', 'js');
        $jsAssetAllowingMerge = $this->getMockForAbstractClass('Magento\Core\Model\Page\Asset\MergeableInterface');
        $jsAssetAllowingMerge->expects($this->any())->method('getContentType')->will($this->returnValue('js'));

        // assets with identical properties should be grouped together
        $this->_object->add('css_asset_one', $cssAsset, array('property' => 'test_value'));
        $this->_object->add('css_asset_two', $cssAsset, array('property' => 'test_value'));

        // assets with different properties should go to different groups
        $this->_object->add('css_asset_three', $cssAsset, array('property' => 'different_value'));
        $this->_object->add('js_asset_one', $jsAsset, array('property' => 'test_value'));

        // assets with identical properties in a different order should be grouped
        $this->_object->add('js_asset_two', $jsAsset, array('property1' => 'value1', 'property2' => 'value2'));
        $this->_object->add('js_asset_three', $jsAsset, array('property2' => 'value2', 'property1' => 'value1'));

        // assets allowing merge should go to separate group regardless of having identical properties
        $this->_object->add('asset_allowing_merge', $jsAssetAllowingMerge, array('property' => 'test_value'));

        $expectedGroups = array(
            array(
                'properties' => array('content_type' => 'unknown', 'can_merge' => false),
                'assets' => array('asset' => $this->_asset),
            ),
            array(
                'properties' => array('property' => 'test_value', 'content_type' => 'css', 'can_merge' => false),
                'assets' => array('css_asset_one' => $cssAsset, 'css_asset_two' => $cssAsset),
            ),
            array(
                'properties' => array('property' => 'different_value', 'content_type' => 'css', 'can_merge' => false),
                'assets' => array('css_asset_three' => $cssAsset),
            ),
            array(
                'properties' => array('property' => 'test_value', 'content_type' => 'js', 'can_merge' => false),
                'assets' => array('js_asset_one' => $jsAsset),
            ),
            array(
                'properties' => array(
                    'property1' => 'value1', 'property2' => 'value2', 'content_type' => 'js', 'can_merge' => false,
                ),
                'assets' => array('js_asset_two' => $jsAsset, 'js_asset_three' => $jsAsset),
            ),
            array(
                'properties' => array('property' => 'test_value', 'content_type' => 'js', 'can_merge' => true),
                'assets' => array('asset_allowing_merge' => $jsAssetAllowingMerge),
            ),
        );

        $this->_assertGroups($expectedGroups, $this->_object->getGroups());
    }
}
