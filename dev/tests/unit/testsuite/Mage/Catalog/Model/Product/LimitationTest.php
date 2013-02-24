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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Mage_Catalog_Model_Product_LimitationTest extends PHPUnit_Framework_TestCase
{
    /**
     * @param int $createNum
     * @param int $totalCount
     * @param string|int $configuredCount
     * @param bool $expected
     * @dataProvider isCreateRestrictedDataProvider
     */
    public function testIsCreateRestricted($createNum, $totalCount, $configuredCount, $expected)
    {
        $resource = $this->getMock('Mage_Catalog_Model_Resource_Product', array('countAll'), array(), '', false);
        $resource->expects($this->any())->method('countAll')->will($this->returnValue($totalCount));

        $config = $this->getMock('Mage_Core_Model_Config', array('getNode'), array(), '', false);
        $config->expects($this->once())->method('getNode')
            ->with(Mage_Catalog_Model_Product_Limitation::XML_PATH_NUM_PRODUCTS)
            ->will($this->returnValue($configuredCount));

        $model = new Mage_Catalog_Model_Product_Limitation($resource, $config);
        $this->assertEquals($expected, $model->isCreateRestricted($createNum));
    }

    /**
     * @return array
     */
    public function isCreateRestrictedDataProvider()
    {
        return array(
            'add 1 product with no limit'            => array(1, 0, '', false),
            'add 1 product with negative limit'      => array(1, 2, -1, false),
            'add 1 product with zero limit'          => array(1, 2, 0, false),
            'add 1 product with count > limit '      => array(1, 2, 1, true),
            'add 1 product with count = limit'       => array(1, 2, 2, true),
            'add 1 product with count < limit'       => array(1, 2, 3, false),
            'add 2 products with count < limit'      => array(2, 2, 3, true),
            'add 2 products with count much < limit' => array(2, 1, 3, false),
        );
    }
}
