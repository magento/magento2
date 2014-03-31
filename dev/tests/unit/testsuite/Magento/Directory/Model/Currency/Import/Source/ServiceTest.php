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
namespace Magento\Directory\Model\Currency\Import\Source;

class ServiceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Directory\Model\Currency\Import\Source\Service
     */
    protected $_model;

    /**
     * @var \Magento\Directory\Model\Currency\Import\Config|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_importConfig;

    protected function setUp()
    {
        $this->_importConfig = $this->getMock(
            'Magento\Directory\Model\Currency\Import\Config',
            array(),
            array(),
            '',
            false
        );
        $this->_model = new \Magento\Directory\Model\Currency\Import\Source\Service($this->_importConfig);
    }

    public function testToOptionArray()
    {
        $this->_importConfig->expects(
            $this->once()
        )->method(
            'getAvailableServices'
        )->will(
            $this->returnValue(array('service_one', 'service_two'))
        );
        $this->_importConfig->expects(
            $this->at(1)
        )->method(
            'getServiceLabel'
        )->with(
            'service_one'
        )->will(
            $this->returnValue('Service One')
        );
        $this->_importConfig->expects(
            $this->at(2)
        )->method(
            'getServiceLabel'
        )->with(
            'service_two'
        )->will(
            $this->returnValue('Service Two')
        );
        $expectedResult = array(
            array('value' => 'service_one', 'label' => 'Service One'),
            array('value' => 'service_two', 'label' => 'Service Two')
        );
        $this->assertEquals($expectedResult, $this->_model->toOptionArray());
        // Makes sure the value is calculated only once
        $this->assertEquals($expectedResult, $this->_model->toOptionArray());
    }
}
