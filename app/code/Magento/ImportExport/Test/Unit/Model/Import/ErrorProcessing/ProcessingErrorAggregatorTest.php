<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ImportExport\Test\Unit\Model\Import\ErrorProcessing;

use Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingError;

class ProcessingErrorAggregatorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingErrorFactory
     * |\PHPUnit\Framework\MockObject\MockObject
     */
    protected $processingErrorFactoryMock;

    /**
     * @var \Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingErrorAggregator
     * |\Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $model;

    /**
     * @var \Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingError
     */
    protected $processingError1;

    /**
     * @var \Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingError
     */
    protected $processingError2;

    /**
     * @var \Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingError
     */
    protected $processingError3;

    /**
     * Preparing mock objects
     */
    protected function setUp(): void
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->processingErrorFactoryMock = $this->createPartialMock(
            \Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingErrorFactory::class,
            ['create']
        );

        $this->processingError1 = $objectManager->getObject(
            \Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingError::class
        );

        $this->processingError2 = $objectManager->getObject(
            \Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingError::class
        );

        $this->processingError3 = $objectManager->getObject(
            \Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingError::class
        );

        $this->processingErrorFactoryMock->expects($this->any())->method('create')->willReturnOnConsecutiveCalls(
            $this->processingError1,
            $this->processingError2,
            $this->processingError3
        );

        $this->model = $objectManager->getObject(
            \Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingErrorAggregator::class,
            [
                'errorFactory' => $this->processingErrorFactoryMock
            ]
        );
    }

    /**
     * Test for method addError
     */
    public function testAddError()
    {
        $result = $this->model->addError(
            'systemException',
            'critical',
            7,
            'Some column name',
            'Message',
            'Description'
        );
        $this->assertEquals($result, $this->model);
    }

    /**
     * Test for method addRowToSkip
     */
    public function testAddRowToSkip()
    {
        $this->model->addRowToSkip(7);
        $result = $this->model->isRowInvalid(7);
        $this->assertTrue($result);
    }

    /**
     * Test for method addErrorMessageTemplate
     */
    public function testAddErrorMessageTemplate()
    {
        $this->model->addErrorMessageTemplate('columnNotFound', 'Template: No column');
        $this->model->addError('systemException');
        $this->model->addError('columnNotFound', 'critical', 7, 'Some column name', null, 'Description');
        $this->model->addError('columnEmptyHeader', 'not-critical', 4, 'Some column name', 'No header', 'Description');
        $result = $this->model->getRowsGroupedByErrorCode(['systemException'], [], false);
        $expectedResult = ['systemException' => [0 => 1]];
        $this->assertEquals($expectedResult, $result);
    }

    /**
     * Test for method isRowInvalid. Expected true result.
     * @dataProvider isRowInvalidDataProvider
     */
    public function testIsRowInvalid($errorLevel, $rowNumber, $isValid)
    {
        $this->model->addError('systemException', $errorLevel, $rowNumber, 'Column name', 'Message', 'Description');
        $result = $this->model->isRowInvalid($rowNumber);
        $this->assertEquals($isValid, $result);
    }

    /**
     * @return array
     */
    public function isRowInvalidDataProvider()
    {
        return [
            [ProcessingError::ERROR_LEVEL_CRITICAL, 7, true],
            [ProcessingError::ERROR_LEVEL_NOT_CRITICAL, 8, false],
            [ProcessingError::ERROR_LEVEL_NOTICE, 9, false],
            [ProcessingError::ERROR_LEVEL_WARNING, 10, false]
        ];
    }

    /**
     * Test for method getInvalidRowsCount. Expected 0 invalid rows.
     */
    public function testGetInvalidRowsCountZero()
    {
        $rowsNumber = $this->model->getInvalidRowsCount();
        $this->assertEquals($rowsNumber, 0);
    }

    /**
     * Test for method getInvalidRowsCount. Expected 1 invalid row.
     */
    public function testGetInvalidRowsCountOne()
    {
        $this->model->addError('systemException', 'critical', 7, 'Some column name', 'Message', 'Description');
        $rowsNumber = $this->model->getInvalidRowsCount();
        $this->assertEquals($rowsNumber, 1);
    }

    /**
     * Test for method getInvalidRowsCount. Expected 2 invalid rows.
     */
    public function testGetInvalidRowsCountTwo()
    {
        $this->model->addError('systemException');
        $this->model->addError('systemException', 'critical', 8, 'Some column name', 'Message', 'Description');
        $this->model->addError('systemException', 'critical', 7, 'Some column name', 'Message', 'Description');
        $rowsNumber = $this->model->getInvalidRowsCount();
        $this->assertEquals($rowsNumber, 2);
    }

    /**
     * Test for method initValidationStrategy.
     */
    public function testInitValidationStrategy()
    {
        $this->model->initValidationStrategy('validation-stop-on-errors', 5);
        $this->assertEquals(5, $this->model->getAllowedErrorsCount());
    }

    /**
     * Test for method initValidationStrategy.
     */
    public function testInitValidationStrategyExceed()
    {
        $this->model->addError('systemException', 'not-critical', 7, 'Some column name', 'Message', 'Description');
        $this->model->addError('systemException', 'not-critical', 4, 'Some column name', 'Message', 'Description');
        $this->model->addError('systemException', 'not-critical', 2, 'Some column name', 'Message', 'Description');
        $this->model->initValidationStrategy('validation-stop-on-errors', 2);
        $result = $this->model->isErrorLimitExceeded();
        $this->assertTrue($result);
    }

    /**
     * Test for method initValidationStrategy. Expected exception due null incoming parameter
     */
    public function testInitValidationStrategyException()
    {
        $this->expectException(\Magento\Framework\Exception\LocalizedException::class);
        $this->model->initValidationStrategy(null);
    }

    /**
     * Test for method isErrorLimitExceeded. Expects error limit exceeded.
     */
    public function testIsErrorLimitExceededTrue()
    {
        $this->model->addError('systemException');
        $this->model->addError('systemException', 'not-critical', 7, 'Some column name', 'Message', 'Description');
        $this->model->addError('systemException', 'not-critical', 4, 'Some column name', 'Message', 'Description');
        $result = $this->model->isErrorLimitExceeded();
        $this->assertTrue($result);
    }

    /**
     * Test for method isErrorLimitExceeded. Unexpects error limit exceeded.
     */
    public function testIsErrorLimitExceededFalse()
    {
        $this->model->initValidationStrategy('validation-stop-on-errors', 5);
        $this->model->addError('systemException');
        $this->model->addError('systemException', 'critical', 7, 'Some column name', 'Message', 'Description');
        $this->model->addError('systemException', 'critical', 4, 'Some column name', 'Message', 'Description');
        $result = $this->model->isErrorLimitExceeded();
        $this->assertFalse($result);
    }

    /**
     * Test for method hasFatalExceptions.
     */
    public function testHasFatalExceptionsTrue()
    {
        $this->model->addError('systemException');
        $this->model->addError('columnNotFound', 'critical', 7, 'Some column name', 'Message', 'Description');
        $this->model->addError('columnEmptyHeader', 'not-critical', 4, 'Some column name', 'Message', 'Description');
        $result = $this->model->hasFatalExceptions();
        $this->assertTrue($result);
    }

    /**
     * Test for method hasFatalExceptions. Expects no any fatal exceptions
     */
    public function testHasFatalExceptionsFalse()
    {
        $this->model->addError('systemException', 'not-critical', 4, 'Some column name', 'Message', 'Description');
        $result = $this->model->hasFatalExceptions();
        $this->assertFalse($result);
    }

    /**
     * Test for method hasToBeTerminated. Unexpects any errors and termination.
     */
    public function testHasToBeTerminatedFalse()
    {
        $result = $this->model->hasToBeTerminated();
        $this->assertFalse($result);
    }

    /**
     * Test for method hasToBeTerminated. Expects errors and execute termination.
     */
    public function testHasToBeTerminatedTrue()
    {
        $this->model->addError('systemException', 'not-critical', 4, 'Some column name', 'Message', 'Description');
        $result = $this->model->hasToBeTerminated();
        $this->assertTrue($result);
    }

    /**
     * Test for method getAllErrors
     */
    public function testGetAllErrors()
    {
        $this->model->addError('systemException', 'not-critical', 4, 'Some column name', 'Message', 'Description');
        $this->model->addError('systemException', 'not-critical', 5, 'Some column name', 'Message', 'Description');
        $this->model->addError('systemException', 'not-critical', 6, 'Some column name', 'Message', 'Description');
        $result = $this->model->getAllErrors();
        //check if is array of objects
        $this->assertIsArray($result);
        $this->assertCount(3, $result);
        $this->assertInstanceOf(\Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingError::class, $result[0]);
    }

    /**
     * Test for method getErrorByRowNumber
     */
    public function testGetErrorByRowNumber()
    {
        $this->model->addError('systemException1', 'not-critical', 1);
        $this->model->addError('systemException2', 'not-critical', 1);
        $this->model->addError('systemException3', 'not-critical', 2);
        $result = $this->model->getErrorByRowNumber(1);

        $this->assertIsArray($result);
        $this->assertCount(2, $result);
        $this->assertInstanceOf(\Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingError::class, $result[0]);
        $this->assertInstanceOf(\Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingError::class, $result[1]);
        $this->assertEquals('systemException1', $result[0]->getErrorCode());
        $this->assertEquals('systemException2', $result[1]->getErrorCode());
    }

    /**
     * Test logic to prevent adding an identical error more than once.
     * The error has to have the same error code for the same row number
     */
    public function testAddTheSameErrorTwice()
    {
        $this->processingErrorFactoryMock->expects($this->any())->method('create')->willReturnOnConsecutiveCalls(
            $this->processingError1,
            $this->processingError2
        );
        $this->model->addError('systemException', 'not-critical', 1);
        $this->model->addError('systemException', 'not-critical', 1);
        $result = $this->model->getErrorByRowNumber(1);

        $this->assertCount(1, $result);
        $this->assertEquals(1, $this->model->getErrorsCount());
    }

    /**
     * Test for method getErrorsByCode. Expects receive errors with code, which present in incoming parameter.
     */
    public function testGetErrorsByCodeInArray()
    {
        $this->model->addError('systemException', 'not-critical', 4, 'Some column name', 'Message', 'Description');
        $this->model->addError('columnNotFound', 'critical', 7, 'Some column name', 'No column', 'Description');
        $this->model->addError('systemException', 'not-critical', 9, 'Some column name', 'Message', 'Description');
        $result = $this->model->getErrorsByCode(['systemException']);
        $this->assertCount(2, $result);
    }

    /**
     * Test for method getErrorsByCode. Unexpects receive errors with code, which present in incoming parameter.
     */
    public function testGetErrorsByCodeNotInArray()
    {
        $this->model->addError('columnNotFound', 'not-critical', 4, 'Some column name', 'Message', 'Description');
        $this->model->addError('columnNotFound', 'critical', 7, 'Some column name', 'No column', 'Description');
        $this->model->addError('columnEmptyHeader', 'not-critical', 5, 'Some column name', 'No header', 'Description');
        $result = $this->model->getErrorsByCode(['systemException']);
        $this->assertCount(0, $result);
    }

    /**
     * Test for method getRowsGroupedByErrorCode. Expects errors.
     *
     * @param array $params
     * @param array $expectedResult
     *
     * @dataProvider getRowsGroupedByErrorCodeWithErrorsDataProvider
     */
    public function testGetRowsGroupedByErrorCodeWithErrors(array $params = [], array $expectedResult = [])
    {
        $this->model->addError('systemException');
        $this->model->addError('columnNotFound', 'critical', 7, 'Some column name', 'No column', 'Description');
        $this->model->addError('columnEmptyHeader', 'not-critical', 4, 'Some column name', 'No header', 'Description');

        $result = call_user_func_array([$this->model, 'getRowsGroupedByErrorCode'], $params);

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * @return array
     */
    public function getRowsGroupedByErrorCodeWithErrorsDataProvider()
    {
        $errorCode1 = 'systemException';
        $errorCode2 = 'columnNotFound';
        $errorCode3 = 'columnEmptyHeader';

        $message1 = 'systemException';
        $message2 = 'No column';
        $message3 = 'No header';

        return [
            [
                [[$errorCode1]],
                [$message1 => [1]]
            ],
            [
                [[], [$errorCode2]],
                [$message1 => [1], $message3 => [5]]
            ],
            [
                [[$errorCode3, $errorCode2], [$errorCode2]],
                [$message3 => [5]]
            ],
            [
                [[], []],
                [$message1 => [1], $message2 => [8], $message3 => [5]]
            ],

            [
                [[$errorCode1], [], false],
                [$errorCode1 => [1]]
            ],
            [
                [[], [$errorCode2], false],
                [$errorCode1 => [1], $errorCode3 => [5]]
            ],
            [
                [[$errorCode3, $errorCode2], [$errorCode2], false],
                [$errorCode3 => [5]]
            ],
            [
                [[], [], false],
                [$errorCode1 => [1], $errorCode2 => [8], $errorCode3 => [5]]
            ],
        ];
    }

    /**
     * Test for method getRowsGroupedByErrorCode. Unexpects errors.
     */
    public function testGetRowsGroupedByErrorCodeNoErrors()
    {
        $result = $this->model->getRowsGroupedByErrorCode();
        $this->assertIsArray($result);
        $this->assertCount(0, $result);
    }

    /**
     * Test for method getAllowedErrorsCount.
     */
    public function testGetAllowedErrorsCount()
    {
        $this->assertEquals($this->model->getAllowedErrorsCount(), 0);
    }

    /**
     * Test for method clear.
     */
    public function testClear()
    {
        $this->model->addError('systemException', 'not-critical', 4, 'Some column name', 'Message', 'Description');
        $this->model->addError('columnNotFound', 'critical', 7, 'Some column name', 'No column', 'Description');
        $this->model->clear();
        $result = $this->model->getAllErrors();
        $this->assertEquals([], $result);
    }

    /**
     * Test for method getErrorsCount
     */
    public function testGetErrorsCount()
    {
        $this->model->addError('systemException', 'not-critical', 4, 'Some column name', 'Message', 'Description');
        $this->model->addError('columnNotFound', 'critical', 7, 'Some column name', 'No column', 'Description');
        $this->model->addError('systemException', 'not-critical', 9, 'Some column name', 'Message', 'Description');
        $result = $this->model->getErrorsCount(['critical']);
        $this->assertEquals($result, 1);
    }
}
