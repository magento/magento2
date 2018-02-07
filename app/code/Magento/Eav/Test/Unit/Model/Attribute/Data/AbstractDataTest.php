<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Eav\Test\Unit\Model\Attribute\Data;

use Magento\Eav\Model\Attribute\Data\Text;

class AbstractDataTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Eav\Model\Attribute\Data\AbstractData
     */
    protected $model;

    protected function setUp()
    {
        $timezoneMock = $this->getMock('\Magento\Framework\Stdlib\DateTime\TimezoneInterface');
        $loggerMock = $this->getMock('\Psr\Log\LoggerInterface', [], [], '', false);
        $localeResolverMock = $this->getMock('\Magento\Framework\Locale\ResolverInterface');
        $stringMock = $this->getMock('\Magento\Framework\Stdlib\StringUtils', [], [], '', false);

        /* testing abstract model through its child */
        $this->model = new Text($timezoneMock, $loggerMock, $localeResolverMock, $stringMock);
    }

    /**
     * @covers \Magento\Eav\Model\Attribute\Data\AbstractData::getEntity
     * @covers \Magento\Eav\Model\Attribute\Data\AbstractData::setEntity
     */
    public function testGetEntity()
    {
        $entityMock = $this->getMock('\Magento\Framework\Model\AbstractModel', [], [], '', false);
        $this->model->setEntity($entityMock);
        $this->assertEquals($entityMock, $this->model->getEntity());
    }

    /**
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage Entity object is undefined
     *
     * @covers \Magento\Eav\Model\Attribute\Data\AbstractData::getEntity
     */
    public function testGetEntityWhenEntityNotSet()
    {
        $this->model->getEntity();
    }

    /**
     * @covers \Magento\Eav\Model\Attribute\Data\AbstractData::getExtractedData
     * @covers \Magento\Eav\Model\Attribute\Data\AbstractData::setExtractedData
     *
     * @param string $index
     * @param mixed $expectedResult
     *
     * @dataProvider extractedDataDataProvider
     */
    public function testGetExtractedData($index, $expectedResult)
    {
        $extractedData = ['index' => 'value', 'otherIndex' => 'otherValue'];
        $this->model->setExtractedData($extractedData);
        $this->assertEquals($expectedResult, $this->model->getExtractedData($index));
    }

    /**
     * @return array
     */
    public function extractedDataDataProvider()
    {
        return [
            [
                'index' => 'index',
                'expectedResult' => 'value',
            ],
            [
                'index' => null,
                'expectedResult' => ['index' => 'value', 'otherIndex' => 'otherValue']
            ],
            [
                'index' => 'customIndex',
                'expectedResult' => null
            ]
        ];
    }

    /**
     * @covers \Magento\Eav\Model\Attribute\Data\AbstractData::_getRequestValue
     *
     * @param string $requestScope
     * @param string $value
     * @param string $expectedResult
     * @param array $params
     * @param bool $requestScopeOnly
     * @dataProvider getRequestValueDataProvider
     */
    public function testGetRequestValue($requestScope, $value, $params, $requestScopeOnly, $expectedResult)
    {
        $requestMock = $this->getMock(
            '\Magento\Framework\App\Request\Http', ['getParams', 'getParam'], [], '', false
        );
        $requestMock->expects($this->any())->method('getParam')->will($this->returnValueMap([
            ['attributeCode', false, $value],
            [$requestScope, $value],
        ]));
        $requestMock->expects($this->any())->method('getParams')->will($this->returnValue($params));

        $attributeMock = $this->getMock('\Magento\Eav\Model\Attribute', [], [], '', false);
        $attributeMock->expects($this->any())->method('getAttributeCode')->will($this->returnValue('attributeCode'));

        $this->model->setAttribute($attributeMock);
        $this->model->setRequestScope($requestScope);
        $this->model->setRequestScopeOnly($requestScopeOnly);
        $this->assertEquals($expectedResult, $this->model->extractValue($requestMock));
    }

    /**
     * @return array
     */
    public function getRequestValueDataProvider()
    {
        return [
            [
                'requestScope' => false,
                'value' => 'value',
                'params' => [],
                'requestScopeOnly' => true,
                'expectedResult' => 'value',
            ],
            [
                'requestScope' => 'scope/scope',
                'value' => 'value',
                'params' => ['scope' => ['scope' => ['attributeCode' => 'data']]],
                'requestScopeOnly' => true,
                'expectedResult' => 'data'
            ],
            [
                'requestScope' => 'scope/scope',
                'value' => 'value',
                'params' => ['scope' => ['scope' => []]],
                'requestScopeOnly' => true,
                'expectedResult' => false
            ],
            [
                'requestScope' => 'scope/scope',
                'value' => 'value',
                'params' => ['scope'],
                'requestScopeOnly' => true,
                'expectedResult' => false
            ],
            [
                'requestScope' => 'scope',
                'value' => 'value',
                'params' => ['otherScope' => 1],
                'requestScopeOnly' => true,
                'expectedResult' => false
            ],
            [
                'requestScope' => 'scope',
                'value' => 'value',
                'params' => ['otherScope' => 1],
                'requestScopeOnly' => false,
                'expectedResult' => 'value'
            ]
        ];
    }
}
