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
        $this->assertSame(array('asset' => $this->_asset, 'asset_new' => $assetNew), $this->_object->getAll());
    }

    public function testHas()
    {
        $this->assertTrue($this->_object->has('asset'));
        $this->assertFalse($this->_object->has('non_existing_asset'));
    }

    public function testAddSameInstance()
    {
        $this->_object->add('asset_clone', $this->_asset);
        $this->assertSame(array('asset' => $this->_asset, 'asset_clone' => $this->_asset), $this->_object->getAll());
    }

    public function testAddOverrideExisting()
    {
        $assetOverridden = new \Magento\Framework\View\Asset\Remote('http://127.0.0.1/magento/test_overridden.css');
        $this->_object->add('asset', $assetOverridden);
        $this->assertSame(array('asset' => $assetOverridden), $this->_object->getAll());
    }

    public function testRemove()
    {
        $this->_object->remove('asset');
        $this->assertSame(array(), $this->_object->getAll());
    }

    public function testGetAll()
    {
        $this->assertSame(array('asset' => $this->_asset), $this->_object->getAll());
    }
}
