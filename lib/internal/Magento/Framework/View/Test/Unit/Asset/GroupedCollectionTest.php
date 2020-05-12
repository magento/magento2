<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\View\Test\Unit\Asset;

use Magento\Framework\View\Asset\AssetInterface;
use Magento\Framework\View\Asset\GroupedCollection;
use Magento\Framework\View\Asset\MergeableInterface;
use Magento\Framework\View\Asset\PropertyGroup;
use Magento\Framework\View\Asset\PropertyGroupFactory;
use Magento\Framework\View\Asset\Remote;
use PHPUnit\Framework\TestCase;

class GroupedCollectionTest extends TestCase
{
    /**
     * @var GroupedCollection
     */
    protected $_object;

    /**
     * @var AssetInterface
     */
    protected $_asset;

    protected function setUp(): void
    {
        $factory = $this->createMock(PropertyGroupFactory::class);
        $factory->expects(
            $this->any()
        )->method(
            'create'
        )->willReturnCallback(
            [$this, 'createAssetGroup']
        );
        $this->_object = new GroupedCollection($factory);
        $this->_asset = new Remote('http://127.0.0.1/magento/test.css');
        $this->_object->add('asset', $this->_asset);
    }

    protected function tearDown(): void
    {
        $this->_object = null;
        $this->_asset = null;
    }

    /**
     * Return newly created asset group. Used as a stub for object manger's creation operation.
     *
     * @param array $arguments
     * @return PropertyGroup
     */
    public function createAssetGroup(array $arguments)
    {
        return new PropertyGroup($arguments['properties']);
    }

    /**
     * Assert that actual asset groups equal to expected ones
     *
     * @param array $expectedGroups
     * @param array $actualGroupObjects
     */
    protected function _assertGroups(array $expectedGroups, array $actualGroupObjects)
    {
        $this->assertIsArray($actualGroupObjects);
        $actualGroups = [];
        /** @var \Magento\Framework\View\Asset\PropertyGroup $actualGroup */
        foreach ($actualGroupObjects as $actualGroup) {
            $this->assertInstanceOf(PropertyGroup::class, $actualGroup);
            $actualGroups[] = ['properties' => $actualGroup->getProperties(), 'assets' => $actualGroup->getAll()];
        }
        $this->assertEquals($expectedGroups, $actualGroups);
    }

    public function testAdd()
    {
        $assetNew = new Remote('http://127.0.0.1/magento/test_new.css');
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
        $cssAsset = new Remote('http://127.0.0.1/style.css', 'css');
        $jsAsset = new Remote('http://127.0.0.1/script.js', 'js');
        $jsAssetAllowingMerge = $this->getMockForAbstractClass(MergeableInterface::class);
        $jsAssetAllowingMerge->expects($this->any())->method('getContentType')->willReturn('js');

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
