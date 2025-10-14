<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

/**
 * Test class for \Magento\ImportExport\Model\Import\Entity\AbstractEntity
 */
namespace Magento\ImportExport\Test\Unit\Model\Import\Entity;

use Magento\ImportExport\Model\Import;
use Magento\ImportExport\Model\Import\AbstractSource;
use Magento\ImportExport\Model\Import\Entity\AbstractEntity;
use Magento\ImportExport\Test\Unit\Model\Import\AbstractImportTestCase;
use PHPUnit\Framework\MockObject\MockObject;

class AbstractTest extends AbstractImportTestCase
{
    /**
     * Abstract import entity model
     *
     * @var AbstractEntity|MockObject
     */
    protected $_model;

    protected function setUp(): void
    {
        parent::setUp();

        $this->_model = $this->getMockBuilder(AbstractEntity::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['_saveValidatedBunches', 'getErrorAggregator'])
            ->getMockForAbstractClass();

        $this->_model->method('getErrorAggregator')->willReturn(
            $this->getErrorAggregatorObject()
        );
    }

    protected function tearDown(): void
    {
        unset($this->_model);

        parent::tearDown();
    }

    /**
     * Create source adapter mock and set it into model object which tested in this class
     *
     * @param array $columns value which will be returned by method getColNames()
     * @return AbstractSource|MockObject
     */
    protected function _createSourceAdapterMock(array $columns)
    {
        /** @var $source \Magento\ImportExport\Model\Import\AbstractSource|MockObject */
        $source = $this->getMockForAbstractClass(
            AbstractSource::class,
            [],
            '',
            false,
            true,
            true,
            ['getColNames']
        );
        $source->expects($this->any())->method('getColNames')->willReturn($columns);
        $this->_model->setSource($source);

        return $source;
    }

    /**
     * Test for method validateData()
     *
     * @covers \Magento\ImportExport\Model\Import\Entity\AbstractEntity::validateData
     */
    public function testValidateDataEmptyColumnName()
    {
        $this->_createSourceAdapterMock(['']);
        $errorAggregator = $this->_model->validateData();
        $this->assertArrayHasKey(
            AbstractEntity::ERROR_CODE_COLUMN_EMPTY_HEADER,
            $errorAggregator->getRowsGroupedByErrorCode()
        );
    }

    /**
     * Test for method validateData() for delete behaviour
     *
     * @covers \Magento\ImportExport\Model\Import\AbstractEntity::validateData
     */
    public function testValidateDataEmptyColumnNameForDeleteBehaviour()
    {
        $this->_createSourceAdapterMock(['']);
        $this->_model->setParameters(['behavior' => Import::BEHAVIOR_DELETE]);
        $errorAggregator = $this->_model->validateData();
        $this->assertEquals(0, $errorAggregator->getErrorsCount());
    }

    /**
     * Test for method validateData() for delete behaviour
     *
     * @covers \Magento\ImportExport\Model\Import\Entity\AbstractEntity::validateData
     */
    public function testValidateDataColumnNameWithWhitespacesForDeleteBehaviour()
    {
        $this->_createSourceAdapterMock(['  ']);
        $this->_model->setParameters(['behavior' => Import::BEHAVIOR_DELETE]);
        $errorAggregator = $this->_model->validateData();
        $this->assertEquals(0, $errorAggregator->getErrorsCount());
    }

    /**
     * Test for method validateData()
     *
     * @covers \Magento\ImportExport\Model\Import\Entity\AbstractEntity::validateData
     */
    public function testValidateDataColumnNameWithWhitespaces()
    {
        $this->_createSourceAdapterMock(['  ']);
        $errorAggregator = $this->_model->validateData();
        $this->assertArrayHasKey(
            AbstractEntity::ERROR_CODE_COLUMN_EMPTY_HEADER,
            $errorAggregator->getRowsGroupedByErrorCode()
        );
    }

    /**
     * Test for method validateData()
     *
     * @covers \Magento\ImportExport\Model\Import\Entity\AbstractEntity::validateData
     */
    public function testValidateDataAttributeNames()
    {
        $this->_createSourceAdapterMock(['_test1']);
        $errorAggregator = $this->_model->validateData();
        $this->assertArrayHasKey(
            AbstractEntity::ERROR_CODE_COLUMN_NAME_INVALID,
            $errorAggregator->getRowsGroupedByErrorCode()
        );
    }

    /**
     * Test for method isNeedToLogInHistory()
     *
     * @covers \Magento\ImportExport\Model\Import\Entity\AbstractEntity::isNeedToLogInHistory
     */
    public function testIsNeedToLogInHistory()
    {
        $this->assertFalse($this->_model->isNeedToLogInHistory());
    }

    /**
     * Test for method isAttributeValid()
     *
     * @dataProvider isAttributeValidDataProvider
     * @covers \Magento\ImportExport\Model\Import\Entity\AbstractEntity::isAttributeValid
     *
     * @param string $attrCode
     * @param array $attrParams
     * @param array $rowData
     * @param int $rowNum
     * @param bool $expectedResult
     */
    public function testIsAttributeValid($attrCode, array $attrParams, array $rowData, $rowNum, $expectedResult)
    {
        $this->_createSourceAdapterMock(['_test1']);
        $this->assertEquals(
            $expectedResult,
            $this->_model->isAttributeValid($attrCode, $attrParams, $rowData, $rowNum)
        );
    }

    /**
     * Data provider for testIsAttributeValid
     *
     * @return array
     */
    public static function isAttributeValidDataProvider()
    {
        return [
            ['created_at', ['type' => 'datetime'], ['created_at' => '2012-02-29'], 1, true],
            ['dob', ['type' => 'datetime'], ['dob' => '29.02.2012'], 1, true],
            ['created_at', ['type' => 'datetime'], ['created_at' => '02/29/2012'], 1, true],
            ['dob', ['type' => 'datetime'], ['dob' => '2012-02-29 21:12:59'], 1, true],
            ['created_at', ['type' => 'datetime'], ['created_at' => '29.02.2012 11:12:59'], 1, true],
            ['dob', ['type' => 'datetime'], ['dob' => '02/29/2012 11:12:59'], 1, true],
            ['created_at', ['type' => 'datetime'], ['created_at' => '2012602-29'], 1, false],
            ['dob', ['type' => 'datetime'], ['dob' => '32.12.2012'], 1, false],
            ['created_at', ['type' => 'datetime'], ['created_at' => '02/30/-2012'], 1, false],
            ['dob', ['type' => 'datetime'], ['dob' => '2012-13-29 21:12:59'], 1, false],
            ['created_at', ['type' => 'datetime'], ['created_at' => '11.02.4 11:12:59'], 1, false],
            ['dob', ['type' => 'datetime'], ['dob' => '02/29/2012 11:12:67'], 1, false]
        ];
    }

    /**
     * Test getCreatedItemsCount()
     */
    public function testGetCreatedItemsCount()
    {
        $this->assertIsInt($this->_model->getCreatedItemsCount());
    }

    /**
     * Test getUpdatedItemsCount()
     */
    public function testGetUpdatedItemsCount()
    {
        $this->assertIsInt($this->_model->getUpdatedItemsCount());
    }

    /**
     * Test getDeletedItemsCount()
     */
    public function testGetDeletedItemsCount()
    {
        $this->assertIsInt($this->_model->getDeletedItemsCount());
    }
}
