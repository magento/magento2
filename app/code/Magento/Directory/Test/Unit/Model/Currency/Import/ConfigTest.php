<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Directory\Test\Unit\Model\Currency\Import;

use Magento\Directory\Model\Currency\Import\Config;
use PHPUnit\Framework\TestCase;

class ConfigTest extends TestCase
{
    /**
     * @var Config
     */
    protected $_model;

    protected function setUp(): void
    {
        $this->_model = new Config(
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
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage($expectedException);
        new Config($configData);
    }

    /**
     * @return array
     */
    public static function constructorExceptionDataProvider()
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

    /**
     * @return array
     */
    public static function getServiceClassDataProvider()
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

    /**
     * @return array
     */
    public static function getServiceLabelDataProvider()
    {
        return ['known' => ['service_one', 'Service One'], 'unknown' => ['unknown', null]];
    }
}
