<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ImportExport\Model\ResourceModel\Import;

/**
 * Test Import Data resource model
 *
 * @magentoDataFixture Magento/ImportExport/_files/import_data.php
 */
class DataTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\ImportExport\Model\ResourceModel\Import\Data
     */
    protected $_model;

    protected function setUp()
    {
        parent::setUp();

        $this->_model = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\ImportExport\Model\ResourceModel\Import\Data'
        );
    }

    /**
     * Test getUniqueColumnData() in case when in data stored in requested column is unique
     */
    public function testGetUniqueColumnData()
    {
        /** @var $objectManager \Magento\TestFramework\ObjectManager */
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

        $expectedBunches = $objectManager->get(
            'Magento\Framework\Registry'
        )->registry(
            '_fixture/Magento_ImportExport_Import_Data'
        );

        $this->assertEquals($expectedBunches[0]['entity'], $this->_model->getUniqueColumnData('entity'));
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
        /** @var $objectManager \Magento\TestFramework\ObjectManager */
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

        $expectedBunches = $objectManager->get(
            'Magento\Framework\Registry'
        )->registry(
            '_fixture/Magento_ImportExport_Import_Data'
        );

        $this->assertEquals($expectedBunches[0]['behavior'], $this->_model->getBehavior());
    }

    /**
     * Test successful getEntityTypeCode()
     */
    public function testGetEntityTypeCode()
    {
        /** @var $objectManager \Magento\TestFramework\ObjectManager */
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

        $expectedBunches = $objectManager->get(
            'Magento\Framework\Registry'
        )->registry(
            '_fixture/Magento_ImportExport_Import_Data'
        );

        $this->assertEquals($expectedBunches[0]['entity'], $this->_model->getEntityTypeCode());
    }
}
