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
 * @category    Magento
 * @package     Mage_Shipping
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Mage_Shipping_Helper_DataTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Mage_Shipping_Helper_Data
     */
    protected $_helper = null;

    public function setUp()
    {
        $this->_helper = new Mage_Shipping_Helper_Data;
    }

    /**
     * @param string $modelName
     * @param string $getIdMethod
     * @param int $entityId
     * @param string $code
     * @param string $expected
     * @dataProvider getTrackingPopupUrlBySalesModelDataProvider
     */
    public function testGetTrackingPopupUrlBySalesModel($modelName, $getIdMethod, $entityId, $code, $expected)
    {
        $model = $this->getMock($modelName, array($getIdMethod, 'getProtectCode'));
        $model->expects($this->any())
            ->method($getIdMethod)
            ->will($this->returnValue($entityId));
        $model->expects($this->any())
            ->method('getProtectCode')
            ->will($this->returnValue($code));

        $actual = $this->_helper->getTrackingPopupUrlBySalesModel($model);
        $this->assertEquals($expected, $actual);
    }

    /**
     * @return array
     */
    public function getTrackingPopupUrlBySalesModelDataProvider()
    {
        return array(
            array(
                'Mage_Sales_Model_Order',
                'getId',
                42,
                'abc',
                'http://localhost/index.php/shipping/tracking/popup/hash/b3JkZXJfaWQ6NDI6YWJj/'
            ),
            array(
                'Mage_Sales_Model_Order_Shipment',
                'getId',
                42,
                'abc',
                'http://localhost/index.php/shipping/tracking/popup/hash/c2hpcF9pZDo0MjphYmM,/'
            ),
            array(
                'Mage_Sales_Model_Order_Shipment_Track',
                'getEntityId',
                42,
                'abc',
                'http://localhost/index.php/shipping/tracking/popup/hash/dHJhY2tfaWQ6NDI6YWJj/'
            )
        );
    }
}
