<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ImportExport\Test\Unit\Model\Import\ErrorProcessing;

class ProcessingErrorAggregatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingErrorFactory
     * |\PHPUnit_Framework_MockObject_MockObject
     */
    protected $processingErrorFactoryMock;

    /**
     * @var \Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingErrorAggregator
     * |\Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $model;

    /**
     * @var \Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingError|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $processingErrorMock1;

    /**
     * @var \Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingError|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $processingErrorMock2;

    /**
     * @var \Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingError|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $processingErrorMock3;

    public function setUp()
    {
        $this->processingErrorFactoryMock = $this->getMock(
            '\Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingErrorFactory',
            ['create'],
            [],
            '',
            false
        );

        $this->processingErrorMock1 = $this->getMock(
            '\Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingError',
            null,
            [],
            '',
            false
        );

        $this->processingErrorMock2 = $this->getMock(
            '\Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingError',
            null,
            [],
            '',
            false
        );

        $this->processingErrorMock3 = $this->getMock(
            '\Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingError',
            null,
            [],
            '',
            false
        );

        $this->processingErrorFactoryMock->expects($this->any())->method('create')->willReturnOnConsecutiveCalls(
            $this->processingErrorMock1,
            $this->processingErrorMock2,
            $this->processingErrorMock3
        );

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->model = $objectManager->getObject(
            '\Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingErrorAggregator',
            [
                'errorFactory' => $this->processingErrorFactoryMock
            ]
        );
    }

    public function testAddError()
    {
        $this->model->addError('systemException', 'critical', 7, 'Some column name', 'Message', 'Description');
    }

    public function testAddErrorNullMessageError()
    {
        $this->model->addErrorMessageTemplate('systemException', 'template');
        $this->model->addError('systemException', 'critical', 7, 'Some column name', null, null);
    }

    public function testAddErrorMessageTemplate()
    {
        $this->model->addErrorMessageTemplate('columnNotFound', 'Template: No column');
        $this->model->addError('systemException');
        $this->model->addError('columnNotFound', 'critical', 7, 'Some column name', null, 'Description');
        $this->model->addError('columnEmptyHeader', 'not-critical', 4, 'Some column name', 'No header', 'Description');
        $result = $this->model->getRowsGroupedByErrorCode(['systemException']);
        $expectedResult = [
            'Template: No column' => [8],
            'No header' => [5]
        ];
        $this->assertEquals($expectedResult, $result);
    }

    public function testIsRowInvalidTrue()
    {
        $this->model->addError('systemException', 'critical', 7, 'Some column name', 'Message', 'Description');
        $result = $this->model->isRowInvalid(7);
        $this->assertTrue($result);
    }

    public function testIsRowInvalidFalse()
    {
        $this->model->addError('systemException');
        $result = $this->model->isRowInvalid(8);
        $this->assertFalse($result);
    }

    public function testGetInvalidRowsCountZero()
    {
        $rowsNumber = $this->model->getInvalidRowsCount();
        $this->assertEquals($rowsNumber, 0);
    }

    public function testGetInvalidRowsCountOne()
    {
        $this->model->addError('systemException', 'critical', 7, 'Some column name', 'Message', 'Description');
        $rowsNumber = $this->model->getInvalidRowsCount();
        $this->assertEquals($rowsNumber, 1);
    }

    public function testGetInvalidRowsCountTwo()
    {
        $this->model->addError('systemException');
        $this->model->addError('systemException', 'critical', 8, 'Some column name', 'Message', 'Description');
        $this->model->addError('systemException', 'critical', 7, 'Some column name', 'Message', 'Description');
        $rowsNumber = $this->model->getInvalidRowsCount();
        $this->assertEquals($rowsNumber, 2);
    }

    public function testInitValidationStrategy()
    {
        $this->model->initValidationStrategy('validation-stop-on-errors', 5);
        $this->assertEquals(5, $this->model->getAllowedErrorsCount());
    }

    public function testInitValidationStrategyExceed()
    {
        $this->model->addError('systemException', 'not-critical', 7, 'Some column name', 'Message', 'Description');
        $this->model->addError('systemException', 'not-critical', 4, 'Some column name', 'Message', 'Description');
        $this->model->addError('systemException', 'not-critical', 2, 'Some column name', 'Message', 'Description');
        $this->model->initValidationStrategy('validation-stop-on-errors', 2);
        $result = $this->model->isErrorLimitExceeded();
        $this->assertTrue($result);
    }

    public function testInitValidationStrategyException()
    {
        $this->setExpectedException('\Magento\Framework\Exception\LocalizedException');
        $this->model->initValidationStrategy(null);
    }

    public function testIsErrorLimitExceededTrue()
    {
        $this->model->addError('systemException');
        $this->model->addError('systemException', 'not-critical', 7, 'Some column name', 'Message', 'Description');
        $this->model->addError('systemException', 'not-critical', 4, 'Some column name', 'Message', 'Description');
        $result = $this->model->isErrorLimitExceeded();
        $this->assertTrue($result);
    }

    public function testIsErrorLimitExceededFalse()
    {
        $this->model->addError('systemException');
        $this->model->addError('systemException', 'critical', 7, 'Some column name', 'Message', 'Description');
        $this->model->addError('systemException', 'critical', 4, 'Some column name', 'Message', 'Description');
        $result = $this->model->isErrorLimitExceeded();
        $this->assertFalse($result);
    }

    public function testHasFatalExceptionsTrue()
    {
        $this->model->addError('systemException');
        $this->model->addError('columnNotFound', 'critical', 7, 'Some column name', 'Message', 'Description');
        $this->model->addError('columnEmptyHeader', 'not-critical', 4, 'Some column name', 'Message', 'Description');
        $result = $this->model->hasFatalExceptions();
        $this->assertTrue($result);
    }

    public function testHasFatalExceptionsFalse()
    {
        $this->model->addError('systemException', 'not-critical', 4, 'Some column name', 'Message', 'Description');
        $result = $this->model->hasFatalExceptions();
        $this->assertFalse($result);
    }

    public function testHasToBeTerminatedFalse()
    {
        $result = $this->model->hasToBeTerminated();
        $this->assertFalse($result);
    }

    public function testHasToBeTerminatedTrue()
    {
        $this->model->addError('systemException', 'not-critical', 4, 'Some column name', 'Message', 'Description');
        $result = $this->model->hasToBeTerminated();
        $this->assertTrue($result);
    }

    public function testGetAllErrors()
    {
        $this->model->addError('systemException', 'not-critical', 4, 'Some column name', 'Message', 'Description');
        $this->model->addError('systemException', 'not-critical', 5, 'Some column name', 'Message', 'Description');
        $this->model->addError('systemException', 'not-critical', 6, 'Some column name', 'Message', 'Description');
        $result = $this->model->getAllErrors();
        //check if is array of objects
        $this->assertInternalType('array', $result);
        $this->assertCount(3, $result);
        $this->assertInstanceOf('\Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingError', $result[0]);
    }

    public function testGetErrorsByCodeInArray()
    {
        $this->model->addError('systemException', 'not-critical', 4, 'Some column name', 'Message', 'Description');
        $this->model->addError('columnNotFound', 'critical', 7, 'Some column name', 'No column', 'Description');
        $this->model->addError('systemException', 'not-critical', 9, 'Some column name', 'Message', 'Description');
        $result = $this->model->getErrorsByCode(['systemException']);
        $this->assertCount(2, $result);
    }

    public function testGetErrorsByCodeNotInArray()
    {
        $this->model->addError('columnNotFound', 'not-critical', 4, 'Some column name', 'Message', 'Description');
        $this->model->addError('columnNotFound', 'critical', 7, 'Some column name', 'No column', 'Description');
        $this->model->addError('columnEmptyHeader', 'not-critical', 5, 'Some column name', 'No header', 'Description');
        $result = $this->model->getErrorsByCode(['systemException']);
        $this->assertCount(0, $result);
    }

    public function testGetRowsGroupedByErrorCodeWithErrors()
    {
        $this->model->addError('systemException');
        $this->model->addError('columnNotFound', 'critical', 7, 'Some column name', 'No column', 'Description');
        $this->model->addError('columnEmptyHeader', 'not-critical', 4, 'Some column name', 'No header', 'Description');
        $result = $this->model->getRowsGroupedByErrorCode(['systemException']);
        $expectedResult = [
            'No column' => [8],
            'No header' => [5]
        ];
        $this->assertEquals($expectedResult, $result);
    }

    public function testGetRowsGroupedByErrorCodeNoErrors()
    {
        $result = $this->model->getRowsGroupedByErrorCode();
        $this->assertInternalType('array', $result);
        $this->assertCount(0, $result);
    }

    public function testGetAllowedErrorsCount()
    {
        $this->assertEquals($this->model->getAllowedErrorsCount(), 0);
    }

    public function testClear()
    {
        $this->model->addError('systemException', 'not-critical', 4, 'Some column name', 'Message', 'Description');
        $this->model->addError('columnNotFound', 'critical', 7, 'Some column name', 'No column', 'Description');
        $this->model->clear();
        $result = $this->model->getAllErrors();
        $this->assertEquals([], $result);
    }

    public function testGetErrorsCount()
    {
        $this->model->addError('systemException', 'not-critical', 4, 'Some column name', 'Message', 'Description');
        $this->model->addError('columnNotFound', 'critical', 7, 'Some column name', 'No column', 'Description');
        $this->model->addError('systemException', 'not-critical', 9, 'Some column name', 'Message', 'Description');
        $result = $this->model->getErrorsCount(['critical']);
        $this->assertEquals($result, 1);
    }
}
 