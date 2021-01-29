<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ImportExport\Test\Unit\Model\Export\Entity;

class AbstractEavTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Abstract eav export model
     *
     * @var \Magento\ImportExport\Model\Export\Entity\AbstractEav|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $_model;

    /**
     * Attribute codes for tests
     *
     * @var array
     */
    protected $_expectedAttributes = ['firstname', 'lastname'];

    protected function setUp(): void
    {
        $this->_model = $this->getMockForAbstractClass(
            \Magento\ImportExport\Model\Export\Entity\AbstractEav::class,
            [],
            '',
            false,
            true,
            true,
            ['_getExportAttributeCodes', 'getAttributeCollection', 'getAttributeOptions', '__wakeup']
        );

        $this->_model->expects(
            $this->once()
        )->method(
            '_getExportAttributeCodes'
        )->willReturn(
            $this->_expectedAttributes
        );
    }

    protected function tearDown(): void
    {
        unset($this->_model);
    }

    /**
     * Test for method _addAttributesToCollection()
     *
     * @covers \Magento\ImportExport\Model\Export\Entity\AbstractEav::_addAttributesToCollection
     */
    public function testAddAttributesToCollection()
    {
        $method = new \ReflectionMethod($this->_model, '_addAttributesToCollection');
        $method->setAccessible(true);
        $stubCollection = $this->createPartialMock(
            \Magento\Eav\Model\Entity\Collection\AbstractCollection::class,
            ['addAttributeToSelect']
        );
        $stubCollection->expects($this->once())->method('addAttributeToSelect')->with($this->_expectedAttributes);
        $method->invoke($this->_model, $stubCollection);
    }

    /**
     * Test for methods _addAttributeValuesToRow()
     *
     * @covers \Magento\ImportExport\Model\Export\Entity\AbstractEav::_initAttributeValues
     * @covers \Magento\ImportExport\Model\Export\Entity\AbstractEav::_addAttributeValuesToRow
     */
    public function testAddAttributeValuesToRow()
    {
        $testAttributeCode = 'lastname';
        $testAttributeValue = 'value';
        $testAttributeOptions = ['value' => 'option'];
        /** @var $testAttribute \Magento\Eav\Model\Entity\Attribute */
        $testAttribute = $this->getMockForAbstractClass(
            \Magento\Eav\Model\Entity\Attribute\AbstractAttribute::class,
            [],
            '',
            false,
            false,
            false,
            ['__wakeup']
        );
        $testAttribute->setAttributeCode($testAttributeCode);

        $this->_model->expects(
            $this->any()
        )->method(
            'getAttributeCollection'
        )->willReturn(
            [$testAttribute]
        );

        $this->_model->expects(
            $this->any()
        )->method(
            'getAttributeOptions'
        )->willReturn(
            $testAttributeOptions
        );

        /** @var $item \Magento\Framework\Model\AbstractModel|\PHPUnit\Framework\MockObject\MockObject */
        $item = $this->getMockForAbstractClass(
            \Magento\Framework\Model\AbstractModel::class,
            [],
            '',
            false,
            true,
            true,
            ['getData', '__wakeup']
        );
        $item->expects($this->any())->method('getData')->willReturn($testAttributeValue);

        $method = new \ReflectionMethod($this->_model, '_initAttributeValues');
        $method->setAccessible(true);
        $method->invoke($this->_model);

        $method = new \ReflectionMethod($this->_model, '_addAttributeValuesToRow');
        $method->setAccessible(true);
        $row = $method->invoke($this->_model, $item);
        /**
         *  Prepare expected data
         */
        $expected = [];
        foreach ($this->_expectedAttributes as $code) {
            $expected[$code] = $testAttributeValue;
            if ($code == $testAttributeCode) {
                $expected[$code] = $testAttributeOptions[$expected[$code]];
            }
        }

        $this->assertEquals($expected, $row, 'Attributes were not added to result row');
    }
}
