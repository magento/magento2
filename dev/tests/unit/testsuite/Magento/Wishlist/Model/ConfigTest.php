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
namespace Magento\Wishlist\Model;

class ConfigTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Wishlist\Model\Config
     */
    protected $_model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_storeConfig;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_catalogConfig;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_attributeConfig;

    protected function setUp()
    {
        $this->_storeConfig = $this->getMock('Magento\Core\Model\Store\ConfigInterface');
        $this->_catalogConfig = $this->getMock('Magento\Catalog\Model\Config', array(), array(), '', false);
        $this->_attributeConfig = $this->getMock('Magento\Catalog\Model\Attribute\Config', array(), array(), '', false);
        $this->_model = new \Magento\Wishlist\Model\Config(
            $this->_storeConfig, $this->_catalogConfig, $this->_attributeConfig
        );
    }

    public function testGetProductAttributes()
    {
        $this->_catalogConfig
            ->expects($this->once())
            ->method('getProductAttributes')
            ->will($this->returnValue(array('attribute_one', 'attribute_two')))
        ;
        $this->_attributeConfig
            ->expects($this->once())
            ->method('getAttributeNames')
            ->with('wishlist_item')
            ->will($this->returnValue(array('attribute_three')))
        ;
        $expectedResult = array('attribute_one', 'attribute_two', 'attribute_three');
        $this->assertEquals($expectedResult, $this->_model->getProductAttributes());
    }
}
