<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ImportExport\Test\Unit\Model\Import\ErrorProcessing;

class ProcessingErrorTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingError
     */
    protected $model;

    /**
     * Preparing mock objects
     */
    public function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->model = $objectManager->getObject('\Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingError');
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

        $this->model->init(
            $initData['errorCode'],
            $errorLevel,
            $rowNumber,
            $columnName,
            $errorMessage,
            $errorDescription
        );
    }

    /**
     * Data for method testInit
     *
     * @return array
     */
    public function errorMessageInfo()
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
    public function errorCodeData()
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
    public function errorMessageData()
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
    public function rowNumberData()
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
    public function columnNameData()
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
    public function errorLevelData()
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
    public function errorDescriptionData()
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
