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
namespace Magento\Catalog\Model\Resource\Product;

class FlatTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Catalog\Model\Resource\Product\Flat
     */
    protected $_model;

    /**
     * @var \Magento\Store\Model\Store
     */
    protected $_store;

    /**
     * @var \Magento\Framework\StoreManagerInterface
     */
    protected $_storeManagerInterface;

    public function setUp()
    {
        $this->_store = $this->getMock('\Magento\Store\Model\Store', array(), array(), '', false);

        $this->_storeManagerInterface = $this->getMock('\Magento\Framework\StoreManagerInterface');

        $this->_storeManagerInterface->expects(
            $this->any()
        )->method(
            'getStore'
        )->will(
            $this->returnValue($this->_store)
        );

        $this->_storeManagerInterface->expects(
            $this->any()
        )->method(
            'getDefaultStoreView'
        )->will(
            $this->returnValue($this->_store)
        );


        $this->_model = new \Magento\Catalog\Model\Resource\Product\Flat(
            $this->getMock('Magento\Framework\App\Resource', array(), array(), '', false),
            $this->_storeManagerInterface,
            $this->getMock('Magento\Catalog\Model\Config', array(), array(), '', false)
        );
    }

    public function testSetIntStoreId()
    {
        $store = $this->_model->setStoreId(1);
        $storeId = $store->getStoreId();
        $this->assertEquals(1, $storeId);
    }

    public function testSetNotIntStoreId()
    {
        $this->_storeManagerInterface->expects($this->once())->method('getStore');

        $store = $this->_model->setStoreId('test');
        $storeId = $store->getStoreId();
        $this->assertEquals(0, $storeId);
    }
}
