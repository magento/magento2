<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
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
            [
                'service_one' => ['class' => 'Service_One', 'label' => 'Service One'],
                'service_two' => ['class' => 'Service_Two', 'label' => 'Service Two'],
            ]
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
        return [
            'numeric name' => [
                [0 => ['label' => 'Test Label', 'class' => 'Test_Class']],
                'Name for a currency import service has to be specified',
            ],
            'empty name' => [
                ['' => ['label' => 'Test Label', 'class' => 'Test_Class']],
                'Name for a currency import service has to be specified',
            ],
            'missing class' => [
                ['test' => ['label' => 'Test Label']],
                'Class for a currency import service has to be specified',
            ],
            'empty class' => [
                ['test' => ['label' => 'Test Label', 'class' => '']],
                'Class for a currency import service has to be specified',
            ],
            'missing label' => [
                ['test' => ['class' => 'Test_Class']],
                'Label for a currency import service has to be specified',
            ],
            'empty label' => [
                ['test' => ['class' => 'Test_Class', 'label' => '']],
                'Label for a currency import service has to be specified',
            ]
        ];
    }

    public function testGetAvailableServices()
    {
        $this->assertEquals(['service_one', 'service_two'], $this->_model->getAvailableServices());
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
        return ['known' => ['service_one', 'Service_One'], 'unknown' => ['unknown', null]];
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
        return ['known' => ['service_one', 'Service One'], 'unknown' => ['unknown', null]];
    }
}
