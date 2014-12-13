<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

/**
 * Test class for \Magento\ImportExport\Model\Export\AbstractEntity
 */
namespace Magento\ImportExport\Model\Export;

class EntityAbstractTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test for setter and getter of file name property
     *
     * @covers \Magento\ImportExport\Model\Export\AbstractEntity::getFileName
     * @covers \Magento\ImportExport\Model\Export\AbstractEntity::setFileName
     */
    public function testGetFileNameAndSetFileName()
    {
        /** @var $model \Magento\ImportExport\Model\Export\AbstractEntity */
        $model = $this->getMockForAbstractClass(
            'Magento\ImportExport\Model\Export\AbstractEntity',
            [],
            'Stub_UnitTest_Magento_ImportExport_Model_Export_Entity_TestSetAndGet',
            false
        );

        $testFileName = 'test_file_name';

        $fileName = $model->getFileName();
        $this->assertNull($fileName);

        $model->setFileName($testFileName);
        $this->assertEquals($testFileName, $model->getFileName());

        $fileName = $model->getFileName();
        $this->assertEquals($testFileName, $fileName);
    }
}
