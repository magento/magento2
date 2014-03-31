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
namespace Magento\Directory\Model\Currency\Import;

class ConfigTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Directory\Model\Currency\Import\Config
     */
    protected $_model;

    protected function setUp()
    {
        $this->_model = new \Magento\Directory\Model\Currency\Import\Config(
            array(
                'service_one' => array('class' => 'Service_One', 'label' => 'Service One'),
                'service_two' => array('class' => 'Service_Two', 'label' => 'Service Two')
            )
        );
    }

    /**
     * @param array $configData
     * @param string $expectedException
     * @dataProvider constructorExceptionDataProvider
     */
    public function testConstructorException(array $configData, $expectedException)
    {
        $this->setExpectedException('InvalidArgumentException', $expectedException);
        new \Magento\Directory\Model\Currency\Import\Config($configData);
    }

    public function constructorExceptionDataProvider()
    {
        return array(
            'numeric name' => array(
                array(0 => array('label' => 'Test Label', 'class' => 'Test_Class')),
                'Name for a currency import service has to be specified'
            ),
            'empty name' => array(
                array('' => array('label' => 'Test Label', 'class' => 'Test_Class')),
                'Name for a currency import service has to be specified'
            ),
            'missing class' => array(
                array('test' => array('label' => 'Test Label')),
                'Class for a currency import service has to be specified'
            ),
            'empty class' => array(
                array('test' => array('label' => 'Test Label', 'class' => '')),
                'Class for a currency import service has to be specified'
            ),
            'missing label' => array(
                array('test' => array('class' => 'Test_Class')),
                'Label for a currency import service has to be specified'
            ),
            'empty label' => array(
                array('test' => array('class' => 'Test_Class', 'label' => '')),
                'Label for a currency import service has to be specified'
            )
        );
    }

    public function testGetAvailableServices()
    {
        $this->assertEquals(array('service_one', 'service_two'), $this->_model->getAvailableServices());
    }

    /**
     * @param string $serviceName
     * @param mixed $expectedResult
     * @dataProvider getServiceClassDataProvider
     */
    public function testGetServiceClass($serviceName, $expectedResult)
    {
        $this->assertEquals($expectedResult, $this->_model->getServiceClass($serviceName));
    }

    public function getServiceClassDataProvider()
    {
        return array('known' => array('service_one', 'Service_One'), 'unknown' => array('unknown', null));
    }

    /**
     * @param string $serviceName
     * @param mixed $expectedResult
     * @dataProvider getServiceLabelDataProvider
     */
    public function testGetServiceLabel($serviceName, $expectedResult)
    {
        $this->assertEquals($expectedResult, $this->_model->getServiceLabel($serviceName));
    }

    public function getServiceLabelDataProvider()
    {
        return array('known' => array('service_one', 'Service One'), 'unknown' => array('unknown', null));
    }
}
