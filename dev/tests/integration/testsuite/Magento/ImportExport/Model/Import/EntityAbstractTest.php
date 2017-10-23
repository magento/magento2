<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Test class for \Magento\ImportExport\Model\Import\AbstractEntity
 */
namespace Magento\ImportExport\Model\Import;

use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class EntityAbstractTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Test for method _saveValidatedBunches()
     */
    public function testSaveValidatedBunches()
    {
        $filesystem = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create(\Magento\Framework\Filesystem::class);
        $directory = $filesystem->getDirectoryWrite(DirectoryList::ROOT);
        $source = new \Magento\ImportExport\Model\Import\Source\Csv(
            __DIR__ . '/Entity/_files/customers_for_validation_test.csv',
            $directory
        );
        $source->rewind();
        $expected = $source->current();

        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        /** @var $model \Magento\ImportExport\Model\Import\AbstractEntity|\PHPUnit_Framework_MockObject_MockObject */
        $model = $this->getMockForAbstractClass(
            \Magento\ImportExport\Model\Import\AbstractEntity::class,
            [
                $objectManager->get(\Magento\Framework\Stdlib\StringUtils::class),
                $objectManager->get(\Magento\Framework\App\Config\ScopeConfigInterface::class),
                $objectManager->get(\Magento\ImportExport\Model\ImportFactory::class),
                $objectManager->get(\Magento\ImportExport\Model\ResourceModel\Helper::class),
                $objectManager->get(\Magento\Framework\App\ResourceConnection::class),
                $objectManager->get(
                    \Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingErrorAggregatorInterface::class
                )
            ],
            '',
            true,
            false,
            true,
            ['getMasterAttributeCode', 'validateRow', 'getEntityTypeCode']
        );
        $model->expects($this->any())->method('getMasterAttributeCode')->will($this->returnValue("email"));
        $model->expects($this->any())->method('validateRow')->will($this->returnValue(true));
        $model->expects($this->any())->method('getEntityTypeCode')->will($this->returnValue('customer'));

        $model->setSource($source);

        $method = new \ReflectionMethod($model, '_saveValidatedBunches');
        $method->setAccessible(true);
        $method->invoke($model);

        /** @var $dataSourceModel \Magento\ImportExport\Model\ResourceModel\Import\Data */
        $dataSourceModel = $objectManager->get(\Magento\ImportExport\Model\ResourceModel\Import\Data::class);
        $this->assertCount(1, $dataSourceModel->getIterator());

        $bunch = $dataSourceModel->getNextBunch();
        $this->assertEquals($expected, $bunch[0]);

        //Delete created bunch from DB
        $dataSourceModel->cleanBunches();
    }
}
