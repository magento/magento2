<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryImportExport\Test\Integration\Model\Import\Validator;

use Magento\ImportExport\Model\ResourceModel\Import\Data as ImportData;
use Magento\InventoryImportExport\Model\Import\Sources;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

class SkuValidatorTest extends TestCase
{
    /**
     * @var Sources
     */
    private $importer;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $importDataMock;

    /**
     * Setup for Sku Validator Integration Test Class
     */
    public function setUp()
    {
        $this->importDataMock = $this->getMockBuilder(ImportData::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->importer = Bootstrap::getObjectManager()->create(Sources::class, [
            'importData' => $this->importDataMock
        ]);
    }

    /**
     * Tests that with a SKU that deosn't exist in the catalog still passes as valid
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/products.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/sources.php
     */
    public function testNonExistentSkuDoesPassValidation()
    {
        // SKU-50000 is a non-existent SKU but should still pass Sku Validation
        $rowData = $this->buildRowDataArray(
            'default',
            'SKU-50000',
            10,
            1
        );
        $result = $this->importer->validateRow($rowData, 1);
        $this->assertTrue($result);
    }

    /**
     * Tests that with a valid SKU the validation passes correctly as expected
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/products.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/sources.php
     */
    public function testValidSkuDoesPassValidation()
    {
        $rowData = $this->buildRowDataArray(
            'default',
            'SKU-1',
            10,
            1
        );
        $result = $this->importer->validateRow($rowData, 1);
        $this->assertTrue($result);
    }

    /**
     * Return Data array as if Row during an import
     * @param string $sourceCode
     * @param string $sku
     * @param int $qty
     * @param int $status
     * @return array
     */
    private function buildRowDataArray($sourceCode, $sku, $qty, $status)
    {
        return [
            Sources::COL_SOURCE_CODE => $sourceCode,
            Sources::COL_SKU => $sku,
            Sources::COL_QTY => $qty,
            Sources::COL_STATUS => $status,
        ];
    }
}
