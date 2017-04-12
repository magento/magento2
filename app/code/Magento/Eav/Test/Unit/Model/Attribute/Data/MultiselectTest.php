<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Eav\Test\Unit\Model\Attribute\Data;

class MultiselectTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Eav\Model\Attribute\Data\Multiselect
     */
    protected $model;

    protected function setUp()
    {
        $timezoneMock = $this->getMock(\Magento\Framework\Stdlib\DateTime\TimezoneInterface::class);
        $loggerMock = $this->getMock(\Psr\Log\LoggerInterface::class, [], [], '', false);
        $localeResolverMock = $this->getMock(\Magento\Framework\Locale\ResolverInterface::class);

        $this->model = new \Magento\Eav\Model\Attribute\Data\Multiselect(
            $timezoneMock,
            $loggerMock,
            $localeResolverMock
        );
    }

    /**
     * @covers \Magento\Eav\Model\Attribute\Data\Multiselect::extractValue
     *
     * @param mixed $param
     * @param mixed $expectedResult
     * @dataProvider extractValueDataProvider
     */
    public function testExtractValue($param, $expectedResult)
    {
        $requestMock = $this->getMock(\Magento\Framework\App\RequestInterface::class);
        $attributeMock = $this->getMock(\Magento\Eav\Model\Attribute::class, [], [], '', false);

        $requestMock->expects($this->once())->method('getParam')->will($this->returnValue($param));
        $attributeMock->expects($this->once())->method('getAttributeCode')->will($this->returnValue('attributeCode'));

        $this->model->setAttribute($attributeMock);
        $this->assertEquals($expectedResult, $this->model->extractValue($requestMock));
    }

    /**
     * @return array
     */
    public function extractValueDataProvider()
    {
        return [
            [
                'param' => 'param',
                'expectedResult' => ['param'],
            ],
            [
                'param' => false,
                'expectedResult' => false
            ],
            [
                'param' => ['value'],
                'expectedResult' => ['value']
            ]
        ];
    }

    /**
     * @covers \Magento\Eav\Model\Attribute\Data\Multiselect::outputValue
     *
     * @param string $format
     * @param mixed $expectedResult
     * @dataProvider outputValueDataProvider
     */
    public function testOutputValue($format, $expectedResult)
    {
        $entityMock = $this->getMock(\Magento\Framework\Model\AbstractModel::class, [], [], '', false);
        $entityMock->expects($this->once())->method('getData')->will($this->returnValue('value1,value2,'));

        $sourceMock = $this->getMock(
            \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource::class,
            [],
            [],
            '',
            false
        );
        $sourceMock->expects($this->any())->method('getOptionText')->will($this->returnArgument(0));

        $attributeMock = $this->getMock(\Magento\Eav\Model\Attribute::class, [], [], '', false);
        $attributeMock->expects($this->any())->method('getSource')->will($this->returnValue($sourceMock));

        $this->model->setEntity($entityMock);
        $this->model->setAttribute($attributeMock);
        $this->assertEquals($expectedResult, $this->model->outputValue($format));
    }

    /**
     * @return array
     */
    public function outputValueDataProvider()
    {
        return [
            [
                'format' => \Magento\Eav\Model\AttributeDataFactory::OUTPUT_FORMAT_JSON,
                'expectedResult' => 'value1, value2',
            ],
            [
                'format' => \Magento\Eav\Model\AttributeDataFactory::OUTPUT_FORMAT_ONELINE,
                'expectedResult' => 'value1, value2'
            ]
        ];
    }
}
