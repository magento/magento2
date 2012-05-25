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
 * @package     Mage_Sale
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Mage_Sales_Model_Order_Shipment_TrackTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Mage_Sales_Model_Order_Shipment_Track
     */
    protected $_model;

    protected function setUp()
    {
        $this->_model = $this->getMock('Mage_Sales_Model_Order_Shipment_Track', null, array(), '', false);
    }

    public function testAddData()
    {
        $number = 123;

        $this->_model->addData(array(
            'number' => $number,
            'test' => true
        ));

        $this->assertTrue($this->_model->getTest());
        $this->assertEquals($number, $this->_model->getTrackNumber());
    }
}
