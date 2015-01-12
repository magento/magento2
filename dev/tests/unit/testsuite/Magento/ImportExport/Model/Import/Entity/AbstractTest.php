<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Test class for \Magento\ImportExport\Model\Import\Entity\AbstractEntity
 */
namespace Magento\ImportExport\Model\Import\Entity;

class AbstractTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Abstract import entity model
     *
     * @var \Magento\ImportExport\Model\Import\Entity\AbstractEntity|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_model;

    protected function setUp()
    {
        parent::setUp();

        $this->_model = $this->getMockForAbstractClass(
            'Magento\ImportExport\Model\Import\Entity\AbstractEntity',
            [],
            '',
            false,
            true,
            true,
            ['_saveValidatedBunches']
        );
    }

    protected function tearDown()
    {
        unset($this->_model);

        parent::tearDown();
    }

    /**
     * Create source adapter mock and set it into model object which tested in this class
     *
     * @param array $columns value which will be returned by method getColNames()
     * @return \Magento\ImportExport\Model\Import\AbstractSource|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function _createSourceAdapterMock(array $columns)
    {
        /** @var $source \Magento\ImportExport\Model\Import\AbstractSource|\PHPUnit_Framework_MockObject_MockObject */
        $source = $this->getMockForAbstractClass(
            'Magento\ImportExport\Model\Import\AbstractSource',
            [],
            '',
            false,
            true,
            true,
            ['getColNames']
        );
        $source->expects($this->any())->method('getColNames')->will($this->returnValue($columns));
        $this->_model->setSource($source);

        return $source;
    }

    /**
     * Test for method validateData()
     *
     * @covers \Magento\ImportExport\Model\Import\Entity\AbstractEntity::validateData
     * @expectedException \Magento\Framework\Model\Exception
     * @expectedExceptionMessage Columns number: "1" have empty headers
     */
    public function testValidateDataEmptyColumnName()
    {
        $this->_createSourceAdapterMock(['']);
        $this->_model->validateData();
    }

    /**
     * Test for method validateData()
     *
     * @covers \Magento\ImportExport\Model\Import\Entity\AbstractEntity::validateData
     * @expectedException \Magento\Framework\Model\Exception
     * @expectedExceptionMessage Columns number: "1" have empty headers
     */
    public function testValidateDataColumnNameWithWhitespaces()
    {
        $this->_createSourceAdapterMock(['  ']);
        $this->_model->validateData();
    }

    /**
     * Test for method validateData()
     *
     * @covers \Magento\ImportExport\Model\Import\Entity\AbstractEntity::validateData
     * @expectedException \Magento\Framework\Model\Exception
     * @expectedExceptionMessage Column names: "_test1" are invalid
     */
    public function testValidateDataAttributeNames()
    {
        $this->_createSourceAdapterMock(['_test1']);
        $this->_model->validateData();
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
    public function isAttributeValidDataProvider()
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
}
