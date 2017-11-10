<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ImportExport\Model\ResourceModel\Import;

/**
 * Test Import Data resource model
 *
 * @magentoDataFixture Magento/ImportExport/_files/import_data.php
 */
class DataTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\ImportExport\Model\ResourceModel\Import\Data
     */
    protected $_model;

    /**
     * @var \Magento\TestFramework\ObjectManager
     */
    protected $objectManager;

    /**
     * @var array
     */
    protected $expectedBunches;

    protected function setUp()
    {
        parent::setUp();

        $this->_model = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create(\Magento\ImportExport\Model\ResourceModel\Import\Data::class);

        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

        $this->expectedBunches = $this->objectManager
            ->get(\Magento\Framework\Registry::class)
            ->registry('_fixture/Magento_ImportExport_Import_Data');
    }

    /**
     * Test getUniqueColumnData() in case when in data stored in requested column is unique
     */
    public function testGetUniqueColumnData()
    {
        $this->assertEquals($this->expectedBunches[0]['entity'], $this->_model->getUniqueColumnData('entity'));
    }

    /**
     * Test getUniqueColumnData() in case when in data stored in requested column is NOT unique
     *
     * @expectedException \Magento\Framework\Exception\LocalizedException
     */
    public function testGetUniqueColumnDataException()
    {
        $this->_model->getUniqueColumnData('data');
    }

    /**
     * Test successful getBehavior()
     */
    public function testGetBehavior()
    {
        $this->assertEquals($this->expectedBunches[0]['behavior'], $this->_model->getBehavior());
    }

    /**
     * Test successful getEntityTypeCode()
     */
    public function testGetEntityTypeCode()
    {
        $this->assertEquals($this->expectedBunches[0]['entity'], $this->_model->getEntityTypeCode());
    }

    /**
     * Test successful getNextBunch()
     */
    public function testGetNextBunch()
    {
        $firstBunch = $this->_model->getNextBunch();
        $secondBunch = $this->_model->getNextBunch();
        $thirdBunch = $this->_model->getNextBunch();

        $this->assertCount(2, $firstBunch);
        $this->assertCount(1, $secondBunch);

        $this->assertEquals($this->expectedBunches[0]['data'], $firstBunch);
        $this->assertEquals($this->expectedBunches[1]['data'], $secondBunch);

        $this->assertNull($thirdBunch);
    }
}
