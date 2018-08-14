<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Payment\Test\Unit\Model\Source;

use \Magento\Payment\Model\Source\Cctype;

class CctypeTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Payment config model
     *
     * @var \Magento\Payment\Model\Config | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_paymentConfig;

    /**
     * @var Cctype
     */
    protected $_model;

    /**
     * List of allowed Cc types
     *
     * @var array
     */
    protected $_allowedTypes = ['allowed_cc_type'];

    /**
     * Cc type array
     *
     * @var array
     */
    protected $_cctypesArray = ['allowed_cc_type' => 'name'];

    /**
     * Expected cctype array after toOptionArray call
     *
     * @var array
     */
    protected $_expectedToOptionsArray = [['value' => 'allowed_cc_type', 'label' => 'name']];

    protected function setUp()
    {
        $this->_paymentConfig = $this->getMockBuilder(
            \Magento\Payment\Model\Config::class
        )->disableOriginalConstructor()->setMethods([])->getMock();

        $this->_model = new Cctype($this->_paymentConfig);
    }

    public function testSetAndGetAllowedTypes()
    {
        $model = $this->_model->setAllowedTypes($this->_allowedTypes);
        $this->assertEquals($this->_allowedTypes, $model->getAllowedTypes());
    }

    public function testToOptionArrayEmptyAllowed()
    {
        $this->_preparePaymentConfig();
        $this->assertEquals($this->_expectedToOptionsArray, $this->_model->toOptionArray());
    }

    public function testToOptionArrayNotEmptyAllowed()
    {
        $this->_preparePaymentConfig();
        $this->_model->setAllowedTypes($this->_allowedTypes);
        $this->assertEquals($this->_expectedToOptionsArray, $this->_model->toOptionArray());
    }

    private function _preparePaymentConfig()
    {
        $this->_paymentConfig->expects($this->once())->method('getCcTypes')->will(
            $this->returnValue($this->_cctypesArray)
        );
    }
}
