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
namespace Magento\Catalog\Model\Product;

class CartConfigurationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param string $productType
     * @param array $config
     * @param boolean $expected
     * @dataProvider isProductConfiguredDataProvider
     */
    public function testIsProductConfigured($productType, $config, $expected)
    {
        $cartConfiguration = new \Magento\Catalog\Model\Product\CartConfiguration();
        $productMock = $this->getMock('Magento\Catalog\Model\Product', array(), array(), '', false);
        $productMock->expects($this->once())->method('getTypeId')->will($this->returnValue($productType));
        $this->assertEquals($expected, $cartConfiguration->isProductConfigured($productMock, $config));
    }

    public function isProductConfiguredDataProvider()
    {
        return array(
            'simple' => array('simple', array(), false),
            'virtual' => array('virtual', array('options' => true), true),
            'bundle' => array('bundle', array('bundle_option' => 'option1'), true),
            'some_option_type' => array('some_option_type', array(), false)
        );
    }
}
