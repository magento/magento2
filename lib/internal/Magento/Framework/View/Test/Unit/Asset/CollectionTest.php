<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\View\Test\Unit\Asset;

use Magento\Framework\View\Asset\AssetInterface;
use Magento\Framework\View\Asset\Collection;
use Magento\Framework\View\Asset\Remote;
use PHPUnit\Framework\TestCase;

class CollectionTest extends TestCase
{
    /**
     * @var Collection
     */
    protected $_object;

    /**
     * @var AssetInterface
     */
    protected $_asset;

    protected function setUp(): void
    {
        $this->_object = new Collection();
        $this->_asset = new Remote('http://127.0.0.1/magento/test.css');
        $this->_object->add('asset', $this->_asset);
    }

    public function testAdd()
    {
        $assetNew = new Remote('http://127.0.0.1/magento/test.js');
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
        $assetOverridden = new Remote('http://127.0.0.1/magento/test_overridden.css');
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
