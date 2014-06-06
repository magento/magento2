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

namespace Magento\Payment\Model\Source;

class CctypeTest extends \PHPUnit_Framework_TestCase
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

    public function setUp()
    {
        $this->_paymentConfig = $this->getMockBuilder(
            'Magento\Payment\Model\Config'
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
