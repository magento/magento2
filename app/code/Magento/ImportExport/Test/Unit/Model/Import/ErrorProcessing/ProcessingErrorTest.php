<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ImportExport\Test\Unit\Model\Import\ErrorProcessing;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingError;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ProcessingErrorTest extends TestCase
{

    /**
     * @var MockObject|ProcessingError
     */
    protected $model;

    /**
     * Preparing mock objects
     */
    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $this->model = $objectManager->getObject(
            ProcessingError::class
        );
    }

    /**
     * Test for method init.
     *
     * @dataProvider errorMessageInfo
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function testInit($initData)
    {
        $errorLevel = isset($initData['errorLevel']) ? $initData['errorLevel'] : null;
        $rowNumber = isset($initData['rowNumber']) ? $initData['rowNumber'] : null;
        $columnName = isset($initData['columnName']) ? $initData['columnName'] : null;
        $errorMessage = isset($initData['errorMessage']) ? $initData['errorMessage'] : null;
        $errorDescription = isset($initData['errorDescription']) ? $initData['errorDescription'] : null;

        $result = $this->model->init(
            $initData['errorCode'],
            $errorLevel,
            $rowNumber,
            $columnName,
            $errorMessage,
            $errorDescription
        );
        $this->assertNull($result);
    }

    /**
     * Data for method testInit
     *
     * @return array
     */
    public static function errorMessageInfo()
    {
        return [
            [
                [
                    'errorCode' => 5,
                    'errorLevel' => 'critical',
                    'rowNumber' => 7,
                    'columnName' => 25,
                    'errorMessage' => 'some error message',
                    'errorDescription' => 'some error description'
                ]
            ],
            [
                [
                    'errorCode' => 5,
                    'errorLevel' => null,
                    'rowNumber' => null,
                    'columnName' => null,
                    'errorMessage' => null,
                    'errorDescription' => null
                ]
            ],
        ];
    }

    /**
     * Test for method getErrorCode
     *
     * @dataProvider errorCodeData
     */
    public function testGetErrorCode($data, $expectedValue)
    {
        $this->testInit($data);
        $result = $this->model->getErrorCode();
        $this->assertEquals($result, $expectedValue);
    }

    /**
     * Data for method testGetErrorCode
     *
     * @return array
     */
    public static function errorCodeData()
    {
        return [
            [
                ['errorCode' => 5],
                5
            ],
            [
                ['errorCode' => null],
                null
            ],
        ];
    }

    /**
     * Test for method getErrorMessage
     *
     * @dataProvider errorMessageData
     */
    public function testGetErrorMessage($data, $expectedValue)
    {
        $this->testInit($data);
        $result = $this->model->getErrorMessage();
        $this->assertEquals($result, $expectedValue);
    }

    /**
     * Data for method testGetErrorMessage
     *
     * @return array
     */
    public static function errorMessageData()
    {
        return [
            [
                ['errorCode' => 5, 'errorMessage' => 'Some error message'],
                'Some error message'
            ],
            [
                ['errorCode' => 5],
                null
            ],
        ];
    }

    /**
     * Test for method getRowNumber
     *
     * @dataProvider rowNumberData
     */
    public function testGetRowNumber($data, $expectedValue)
    {
        $this->testInit($data);
        $result = $this->model->getRowNumber();
        $this->assertEquals($result, $expectedValue);
    }

    /**
     * Data for method testGetRowNumber
     *
     * @return array
     */
    public static function rowNumberData()
    {
        return [
            [
                ['errorCode' => 5, 'errorMessage' => 'Some error message', 'rowNumber' => 43],
                43
            ],
            [
                ['errorCode' => 5],
                null
            ],
        ];
    }

    /**
     * Test for method getColumnName
     *
     * @dataProvider columnNameData
     */
    public function testGetColumnName($data, $expectedValue)
    {
        $this->testInit($data);
        $result = $this->model->getColumnName();
        $this->assertEquals($result, $expectedValue);
    }

    /**
     * Data for method testGetColumnName
     *
     * @return array
     */
    public static function columnNameData()
    {
        return [
            [
                [
                    'errorCode' => 5,
                    'errorMessage' => 'Some error message',
                    'rowNumber' => 43,
                    'columnName' => 'Some column name'
                ],
                'Some column name'
            ],
            [
                ['errorCode' => 5],
                null
            ],
        ];
    }

    /**
     * Test for method getErrorLevel
     *
     * @dataProvider errorLevelData
     */
    public function testGetErrorLevel($data, $expectedValue)
    {
        $this->testInit($data);
        $result = $this->model->getErrorLevel();
        $this->assertEquals($result, $expectedValue);
    }

    /**
     * Data for method testGetErrorLevel
     *
     * @return array
     */
    public static function errorLevelData()
    {
        return [
            [
                [
                    'errorCode' => 5,
                    'errorMessage' => 'Some error message',
                    'rowNumber' => 43,
                    'columnName' => 'Some column name',
                    'errorLevel' => 'critical'
                ],
                'critical'
            ],
            [
                ['errorCode' => 5],
                null
            ],
        ];
    }

    /**
     * Test for method getErrorDescription
     *
     * @dataProvider errorDescriptionData
     */
    public function testGetErrorDescription($data, $expectedValue)
    {
        $this->testInit($data);
        $result = $this->model->getErrorDescription();
        $this->assertEquals($result, $expectedValue);
    }

    /**
     * Data for method testGetErrorDescription
     *
     * @return array
     */
    public static function errorDescriptionData()
    {
        return [
            [
                [
                    'errorCode' => 5,
                    'errorMessage' => 'Some error message',
                    'rowNumber' => 43,
                    'columnName' => 'Some column name',
                    'errorLevel' => 'critical',
                    'errorDescription' => 'Some error description'
                ],
                'Some error description'
            ],
            [
                ['errorCode' => 5],
                null
            ],
        ];
    }
}
