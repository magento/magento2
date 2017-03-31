<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\DB\DataConverter;

use Magento\Framework\DB\Adapter\Pdo\Mysql;
use Magento\Framework\DB\FieldDataConverter;
use Magento\Framework\DB\Select;
use Magento\Framework\DB\Select\InQueryModifier;
use Magento\Framework\Serialize\Serializer\Serialize;
use Magento\TestFramework\Helper\Bootstrap;

class DataConverterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var InQueryModifier|\PHPUnit_Framework_MockObject_MockObject
     */
    private $queryModifierMock;

    /**
     * @var SerializedToJson
     */
    private $dataConverter;

    /**
     * @var \Magento\Framework\DB\Query\BatchIterator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $iteratorMock;

    /**
     * @var \Magento\Framework\DB\Query\Generator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $queryGeneratorMock;

    /**
     * @var Select|\PHPUnit_Framework_MockObject_MockObject
     */
    private $selectByRangeMock;

    /**
     * @var Mysql|\PHPUnit_Framework_MockObject_MockObject
     */
    private $adapterMock;

    /**
     * @var FieldDataConverter
     */
    private $fieldDataConverter;

    /**
     * Set up before test
     */
    protected function setUp()
    {
        $this->objectManager = Bootstrap::getObjectManager();

        /** @var InQueryModifier $queryModifier */
        $this->queryModifierMock = $this->getMockBuilder(Select\QueryModifierInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['modify'])
            ->getMock();

        $this->dataConverter = $this->objectManager->get(SerializedToJson::class);

        $this->iteratorMock = $this->getMockBuilder(\Magento\Framework\DB\Query\BatchIterator::class)
            ->disableOriginalConstructor()
            ->setMethods(['current', 'valid', 'next'])
            ->getMock();

        $iterationComplete = false;

        // mock valid() call so iterator passes only current() result in foreach invocation
        $this->iteratorMock->expects($this->any())->method('valid')->will(
            $this->returnCallback(
                function () use (&$iterationComplete) {
                    if (!$iterationComplete) {
                        $iterationComplete = true;
                        return true;
                    } else {
                        return false;
                    }
                }
            )
        );

        $this->queryGeneratorMock = $this->getMockBuilder(\Magento\Framework\DB\Query\Generator::class)
            ->disableOriginalConstructor()
            ->setMethods(['generate'])
            ->getMock();

        $this->selectByRangeMock = $this->getMockBuilder(Select::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $this->queryGeneratorMock->expects($this->any())
            ->method('generate')
            ->will($this->returnValue($this->iteratorMock));

        // mocking only current as next() is not supposed to be called
        $this->iteratorMock->expects($this->any())
            ->method('current')
            ->will($this->returnValue($this->selectByRangeMock));

        $this->adapterMock = $this->getMockBuilder(Mysql::class)
            ->disableOriginalConstructor()
            ->setMethods(['fetchAll', 'quoteInto', 'update'])
            ->getMock();

        $this->adapterMock->expects($this->any())
            ->method('quoteInto')
            ->will($this->returnValue('field=value'));

        $this->fieldDataConverter = $this->objectManager->create(
            FieldDataConverter::class,
            [
                'queryGenerator' => $this->queryGeneratorMock,
                'dataConverter' => $this->dataConverter
            ]
        );
    }

    /**
     * Test that exception with valid text is thrown when data is corrupted
     * @expectedException \Magento\Framework\DB\FieldDataConversionException
     * @expectedExceptionMessage Error converting field `value` in table `table` where `id`=2 using
     */
    public function testDataConvertErrorReporting()
    {
        /** @var Serialize $serializer */
        $serializer = $this->objectManager->create(Serialize::class);
        $serializedData = $serializer->serialize(['some' => 'data', 'other' => 'other data']);
        $serializedDataLength = strlen($serializedData);
        $brokenSerializedData = substr($serializedData, 0, $serializedDataLength - 6);
        $rows = [
            ['id' => 1, 'value' => 'N;'],
            ['id' => 2, 'value' => $brokenSerializedData],
        ];

        $this->adapterMock->expects($this->any())
            ->method('fetchAll')
            ->with($this->selectByRangeMock)
            ->will($this->returnValue($rows));

        $this->adapterMock->expects($this->once())
            ->method('update')
            ->with('table', ['value' => 'null'], ['id = ?' => 1]);

        $this->fieldDataConverter->convert($this->adapterMock, 'table', 'id', 'value', $this->queryModifierMock);
    }

    /**
     */
    public function testAlreadyConvertedDataSkipped()
    {
        $rows = [
            ['id' => 2, 'value' => '[]'],
            ['id' => 3, 'value' => '{}'],
            ['id' => 4, 'value' => 'null'],
            ['id' => 5, 'value' => '""'],
            ['id' => 6, 'value' => '0'],
            ['id' => 7, 'value' => 'N;'],
            ['id' => 8, 'value' => '{"valid": "json value"}'],
        ];

        $this->adapterMock->expects($this->any())
            ->method('fetchAll')
            ->with($this->selectByRangeMock)
            ->will($this->returnValue($rows));

        $this->adapterMock->expects($this->once())
            ->method('update')
            ->with('table', ['value' => 'null'], ['id = ?' => 7]);

        $this->fieldDataConverter->convert($this->adapterMock, 'table', 'id', 'value', $this->queryModifierMock);
    }
}
