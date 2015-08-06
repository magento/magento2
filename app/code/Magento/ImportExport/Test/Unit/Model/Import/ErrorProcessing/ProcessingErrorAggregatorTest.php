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
    protected $processingErrorMock;

    public function setUp()
    {
        $this->processingErrorFactoryMock = $this->getMock(
            '\Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingErrorFactory',
            ['create'],
            [],
            '',
            false
        );

        $this->processingErrorMock = $this->getMock(
            '\Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingError',
            null,
            [],
            '',
            false
        );

        $this->processingErrorFactoryMock->expects($this->any())->method('create')->willReturn(
            $this->processingErrorMock
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

    public function testAddErrorNullError()
    {
        $this->model->addErrorMessageTemplate('systemException', 'template');
        $this->model->addError('systemException', 'critical', 7, 'Some column name');
    }

    public function testAddErrorMessageTemplate()
    {
        $this->model->addErrorMessageTemplate('systemException', 'template');
    }

    public function testIsRowInvalidTrue()
    {
        $this->model->addError('systemException', 'critical', 7, 'Some column name', 'Message', 'Description');
        $result = $this->model->isRowInvalid(8);
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
        $this->model->addError('systemException');
        $rowsNumber = $this->model->getInvalidRowsCount();
        $this->assertEquals($rowsNumber, 1);
    }

    public function testGetInvalidRowsCountTwo()
    {
        $this->model->addError('systemException');
        $this->model->addError('systemException', 'critical', 7, 'Some column name', 'Message', 'Description');
        $rowsNumber = $this->model->getInvalidRowsCount();
        $this->assertEquals($rowsNumber, 2);
    }

    public function testInitValidationStrategy()
    {
        $this->model->initValidationStrategy('validation-skip-errors');
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
        $this->model->addError('systemException', 'critical', 7, 'Some column name', 'Message', 'Description');
        $this->model->addError('systemException', 'not-critical', 4, 'Some column name', 'Message', 'Description');
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
        $this->assertEquals(count($result), 3);
    }

    public function testGetErrorsByCodeInArray()
    {
        $this->model->addError('systemException', 'not-critical', 4, 'Some column name', 'Message', 'Description');
        $this->model->getErrorsByCode(['systemException']);
    }

    public function testGetErrorsByCodeNotInArray()
    {
        $this->model->addError('columnNotFound', 'not-critical', 4, 'Some column name', 'Message', 'Description');
        $this->model->getErrorsByCode(['systemException']);
    }

    public function testGetRowsGroupedByErrorCodeWithErrorsWithoutCode()
    {
        $this->model->addError('systemException', 'not-critical', 4, 'Some column name', 'Message', 'Description');
        $this->model->getRowsGroupedByErrorCode();
    }

    public function testGetRowsGroupedByErrorCodeWithErrorsWithCode()
    {
        $this->model->addError('systemException', 'not-critical', 4, 'Some column name', 'Message', 'Description');
        $this->model->getRowsGroupedByErrorCode(['systemException']);
    }

    public function testGetRowsGroupedByErrorCodeNoErrors()
    {
        $this->model->getRowsGroupedByErrorCode();
    }

    public function testGetAllowedErrorsCount()
    {
        $this->assertEquals($this->model->getAllowedErrorsCount(), 0);
    }

    public function testClear()
    {
        $this->model->clear();
    }
}
 