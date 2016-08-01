<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Payment\Test\Unit\Model\Config\Source;

use \Magento\Payment\Model\Config\Source\Cctype;

class CctypeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Payment data
     *
     * @var \Magento\Payment\Model\Config | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_paymentConfig;

    /**
     * @var Cctype
     */
    protected $_model;

    protected function setUp()
    {
        $this->_paymentConfig = $this->getMockBuilder(
            \Magento\Payment\Model\Config::class
        )->disableOriginalConstructor()->setMethods([])->getMock();

        $this->_model = new Cctype($this->_paymentConfig);
    }

    public function testToOptionArray()
    {
        $cctypesArray = ['code' => 'name'];
        $expectedArray = [
            ['value' => 'code', 'label' => 'name'],
        ];
        $this->_paymentConfig->expects($this->once())->method('getCcTypes')->will($this->returnValue($cctypesArray));
        $this->assertEquals($expectedArray, $this->_model->toOptionArray());
    }
}
