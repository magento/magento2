<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Eav\Test\Unit\Model\Attribute\Data;

use Magento\Eav\Model\Attribute;
use Magento\Eav\Model\Attribute\Data\AbstractData;
use Magento\Eav\Model\Attribute\Data\Text;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\Stdlib\StringUtils;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class AbstractDataTest extends TestCase
{
    /**
     * @var AbstractData
     */
    protected $model;

    protected function setUp(): void
    {
        $timezoneMock = $this->getMockForAbstractClass(TimezoneInterface::class);
        $loggerMock = $this->getMockForAbstractClass(LoggerInterface::class);
        $localeResolverMock = $this->getMockForAbstractClass(ResolverInterface::class);
        $stringMock = $this->createMock(StringUtils::class);

        /* testing abstract model through its child */
        $this->model = new Text($timezoneMock, $loggerMock, $localeResolverMock, $stringMock);
    }

    /**
     * @covers \Magento\Eav\Model\Attribute\Data\AbstractData::getEntity
     * @covers \Magento\Eav\Model\Attribute\Data\AbstractData::setEntity
     */
    public function testGetEntity()
    {
        $entityMock = $this->createMock(AbstractModel::class);
        $this->model->setEntity($entityMock);
        $this->assertEquals($entityMock, $this->model->getEntity());
    }

    /**
     *
     * @covers \Magento\Eav\Model\Attribute\Data\AbstractData::getEntity
     */
    public function testGetEntityWhenEntityNotSet()
    {
        $this->expectException('Magento\Framework\Exception\LocalizedException');
        $this->expectExceptionMessage('Entity object is undefined');
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
    public static function extractedDataDataProvider()
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
     * @param string|null $filter
     * @dataProvider getRequestValueDataProvider
     */
    public function testGetRequestValue($requestScope, $value, $params, $requestScopeOnly, $expectedResult, $filter)
    {
        $requestMock = $this->createPartialMock(Http::class, ['getParams', 'getParam']);
        $requestMock->expects($this->any())->method('getParam')->willReturnMap([
            ['attributeCode', false, $value],
            [$requestScope, $value],
        ]);
        $requestMock->expects($this->any())->method('getParams')->willReturn($params);

        $attributeMock = $this->getMockBuilder(Attribute::class)
            ->addMethods(['getInputFilter'])
            ->onlyMethods(['getAttributeCode'])
            ->disableOriginalConstructor()
            ->getMock();
        $attributeMock->expects($this->any())->method('getAttributeCode')->willReturn('attributeCode');
        if ($filter) {
            $attributeMock->expects($this->any())->method('getInputFilter')->willReturn($filter);
        }

        $this->model->setAttribute($attributeMock);
        $this->model->setRequestScope($requestScope);
        $this->model->setRequestScopeOnly($requestScopeOnly);
        $this->assertEquals($expectedResult, $this->model->extractValue($requestMock));
    }

    /**
     * @return array
     */
    public static function getRequestValueDataProvider()
    {
        return [
            [
                'requestScope' => false,
                'value' => 'value',
                'params' => [],
                'requestScopeOnly' => true,
                'expectedResult' => 'value',
                'filter' => null,
            ],
            [
                'requestScope' => 'scope/scope',
                'value' => 'value',
                'params' => ['scope' => ['scope' => ['attributeCode' => 'data']]],
                'requestScopeOnly' => true,
                'expectedResult' => 'data',
                'filter' => null,
            ],
            [
                'requestScope' => 'scope/scope',
                'value' => 'value',
                'params' => ['scope' => ['scope' => []]],
                'requestScopeOnly' => true,
                'expectedResult' => false,
                'filter' => null,
            ],
            [
                'requestScope' => 'scope/scope',
                'value' => 'value',
                'params' => ['scope'],
                'requestScopeOnly' => true,
                'expectedResult' => false,
                'filter' => null,
            ],
            [
                'requestScope' => 'scope',
                'value' => 'value',
                'params' => ['otherScope' => 1],
                'requestScopeOnly' => true,
                'expectedResult' => false,
                'filter' => null,
            ],
            [
                'requestScope' => 'scope',
                'value' => 'value',
                'params' => ['otherScope' => 1],
                'requestScopeOnly' => false,
                'expectedResult' => 'value',
                'filter' => null,
            ],
            [
                'requestScope' => 'scope',
                'value' => '1970-01-01',
                'params' => [],
                'requestScopeOnly' => false,
                'expectedResult' => '1970-01-01',
                'filter' => 'date'
            ]
        ];
    }
}
