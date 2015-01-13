<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Asset;

class GroupedCollectionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\View\Asset\GroupedCollection
     */
    protected $_object;

    /**
     * @var \Magento\Framework\View\Asset\AssetInterface
     */
    protected $_asset;

    protected function setUp()
    {
        $factory = $this->getMock(
            'Magento\Framework\View\Asset\PropertyGroupFactory',
            [],
            [],
            '',
            false,
            false
        );
        $factory->expects(
            $this->any()
        )->method(
            'create'
        )->will(
            $this->returnCallback([$this, 'createAssetGroup'])
        );
        $this->_object = new \Magento\Framework\View\Asset\GroupedCollection($factory);
        $this->_asset = new \Magento\Framework\View\Asset\Remote('http://127.0.0.1/magento/test.css');
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
     * @param array $arguments
     * @return \Magento\Framework\View\Asset\PropertyGroup
     */
    public function createAssetGroup(array $arguments)
    {
        return new \Magento\Framework\View\Asset\PropertyGroup($arguments['properties']);
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
        $actualGroups = [];
        /** @var $actualGroup \Magento\Framework\View\Asset\PropertyGroup */
        foreach ($actualGroupObjects as $actualGroup) {
            $this->assertInstanceOf('Magento\Framework\View\Asset\PropertyGroup', $actualGroup);
            $actualGroups[] = ['properties' => $actualGroup->getProperties(), 'assets' => $actualGroup->getAll()];
        }
        $this->assertEquals($expectedGroups, $actualGroups);
    }

    public function testAdd()
    {
        $assetNew = new \Magento\Framework\View\Asset\Remote('http://127.0.0.1/magento/test_new.css');
        $this->_object->add('asset_new', $assetNew, ['test_property' => 'test_value']);
        $this->assertEquals(['asset' => $this->_asset, 'asset_new' => $assetNew], $this->_object->getAll());
    }

    public function testRemove()
    {
        $this->_object->remove('asset');
        $this->assertEquals([], $this->_object->getAll());
    }

    public function testGetGroups()
    {
        $cssAsset = new \Magento\Framework\View\Asset\Remote('http://127.0.0.1/style.css', 'css');
        $jsAsset = new \Magento\Framework\View\Asset\Remote('http://127.0.0.1/script.js', 'js');
        $jsAssetAllowingMerge = $this->getMockForAbstractClass('Magento\Framework\View\Asset\MergeableInterface');
        $jsAssetAllowingMerge->expects($this->any())->method('getContentType')->will($this->returnValue('js'));

        // assets with identical properties should be grouped together
        $this->_object->add('css_asset_one', $cssAsset, ['property' => 'test_value']);
        $this->_object->add('css_asset_two', $cssAsset, ['property' => 'test_value']);

        // assets with identical properties but empty properties should be grouped together
        $this->_object->add('css_asset_four', $cssAsset, ['property' => 'test_value2', 'junk1' => null]);
        $this->_object->add('css_asset_five', $cssAsset, ['property' => 'test_value2', 'junk2' => '']);

        // assets with different properties should go to different groups
        $this->_object->add('css_asset_three', $cssAsset, ['property' => 'different_value']);
        $this->_object->add('js_asset_one', $jsAsset, ['property' => 'test_value']);

        // assets with identical properties in a different order should be grouped
        $this->_object->add('js_asset_two', $jsAsset, ['property1' => 'value1', 'property2' => 'value2']);
        $this->_object->add('js_asset_three', $jsAsset, ['property2' => 'value2', 'property1' => 'value1']);

        // assets allowing merge should go to separate group regardless of having identical properties
        $this->_object->add('asset_allowing_merge', $jsAssetAllowingMerge, ['property' => 'test_value']);

        $expectedGroups = [
            [
                'properties' => ['content_type' => 'unknown', 'can_merge' => false],
                'assets' => ['asset' => $this->_asset],
            ],
            [
                'properties' => ['property' => 'test_value', 'content_type' => 'css', 'can_merge' => false],
                'assets' => ['css_asset_one' => $cssAsset, 'css_asset_two' => $cssAsset]
            ],
            [
                'properties' => ['property' => 'test_value2', 'content_type' => 'css', 'can_merge' => false],
                'assets' => ['css_asset_four' => $cssAsset, 'css_asset_five' => $cssAsset]
            ],
            [
                'properties' => ['property' => 'different_value', 'content_type' => 'css', 'can_merge' => false],
                'assets' => ['css_asset_three' => $cssAsset]
            ],
            [
                'properties' => ['property' => 'test_value', 'content_type' => 'js', 'can_merge' => false],
                'assets' => ['js_asset_one' => $jsAsset]
            ],
            [
                'properties' => [
                    'property1' => 'value1',
                    'property2' => 'value2',
                    'content_type' => 'js',
                    'can_merge' => false,
                ],
                'assets' => ['js_asset_two' => $jsAsset, 'js_asset_three' => $jsAsset]
            ],
            [
                'properties' => ['property' => 'test_value', 'content_type' => 'js', 'can_merge' => true],
                'assets' => ['asset_allowing_merge' => $jsAssetAllowingMerge]
            ],
        ];

        $this->_assertGroups($expectedGroups, $this->_object->getGroups());
    }
}
