<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Test\Unit\Asset;

class CollectionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\View\Asset\Collection
     */
    protected $_object;

    /**
     * @var \Magento\Framework\View\Asset\AssetInterface
     */
    protected $_asset;

    protected function setUp()
    {
        $this->_object = new \Magento\Framework\View\Asset\Collection();
        $this->_asset = new \Magento\Framework\View\Asset\Remote('http://127.0.0.1/magento/test.css');
        $this->_object->add('asset', $this->_asset);
    }

    public function testAdd()
    {
        $assetNew = new \Magento\Framework\View\Asset\Remote('http://127.0.0.1/magento/test.js');
        $this->_object->add('asset_new', $assetNew);
        $this->assertSame(['asset' => $this->_asset, 'asset_new' => $assetNew], $this->_object->getAll());
    }

    public function testHas()
    {
        $this->assertTrue($this->_object->has('asset'));
        $this->assertFalse($this->_object->has('non_existing_asset'));
    }

    public function testAddSameInstance()
    {
        $this->_object->add('asset_clone', $this->_asset);
        $this->assertSame(['asset' => $this->_asset, 'asset_clone' => $this->_asset], $this->_object->getAll());
    }

    public function testAddOverrideExisting()
    {
        $assetOverridden = new \Magento\Framework\View\Asset\Remote('http://127.0.0.1/magento/test_overridden.css');
        $this->_object->add('asset', $assetOverridden);
        $this->assertSame(['asset' => $assetOverridden], $this->_object->getAll());
    }

    public function testRemove()
    {
        $this->_object->remove('asset');
        $this->assertSame([], $this->_object->getAll());
    }

    public function testGetAll()
    {
        $this->assertSame(['asset' => $this->_asset], $this->_object->getAll());
    }
}
