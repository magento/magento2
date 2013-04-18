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
 * @category    Mage
 * @package     Mage_Shipping
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Mage_Shipping_Model_Carrier_Service_ConfigTest extends PHPUnit_Framework_TestCase
{

    /**
     * @dataProvider configData
     */
    public function testGetConfigData($config, $query, $expected)
    {
        $config = new Mage_Shipping_Model_Carrier_Service_Config($config);

        $result = $config->getConfigData($query);

        $this->assertSame($expected, $result);
    }

    public function testNoConfigSet()
    {
        $config = new Mage_Shipping_Model_Carrier_Service_Config(array());

        $result = $config->getConfigData('something');

        $this->assertNull($result);
    }

    public function configData()
    {
        return array(
            'simple' => array(
                'config' => array(
                    'active' => true
                ),
                'query' => 'active',
                'expected' => true,
            ),
            'nested' => array(
                'config' => array(
                    'active' => true,
                    'something' => array(
                        'very' => array(
                            'nested' => 'nested value'
                        )
                    )
                ),
                'query' => 'something/very/nested',
                'expected' => 'nested value',
            ),
            'missing' => array(
                'config' => array(
                    'active' => true,
                ),
                'query' => 'something/that/doesnt_exist',
                'expected' => null,
            ),
        );
    }

}