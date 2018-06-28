<?php
/**
 * Copyright :copyright: Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalog\Test\Integration\Bulk;

use Magento\InventoryCatalogApi\Api\DefaultSourceProviderInterface;
use Magento\InventoryCatalogApi\Model\BulkInventoryTransferValidatorInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

class InventoryTransferValidatorTest extends TestCase
{
    /**
     * @var BulkInventoryTransferValidatorInterface
     */
    private $bulkInventoryTransferValidator;

    /**
     * @var DefaultSourceProviderInterface
     */
    private $defaultSourceProvider;

    public function setUp()
    {
        parent::setUp();
        $this->bulkInventoryTransferValidator =
            Bootstrap::getObjectManager()->get(BulkInventoryTransferValidatorInterface::class);
        $this->defaultSourceProvider = Bootstrap::getObjectManager()->get(DefaultSourceProviderInterface::class);
    }

    /**
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/sources.php
     */
    public function testNotExistingSources()
    {
        $skus = ['SKU-1'];

        $validationResult = $this->bulkInventoryTransferValidator->validate($skus, 'non-existing-source', false);

        self::assertFalse(
            $validationResult->isValid(),
            'Validation did not detect invalid source codes'
        );

        $errors = $validationResult->getErrors();

        self::assertEquals(
            'Source %sourceCode does not exist',
            $errors[0]->getText(),
            'Unexpected error message from validator'
        );
    }

    /**
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/sources.php
     */
    public function testExistingSources()
    {
        $skus = ['SKU-1'];

        $validationResult = $this->bulkInventoryTransferValidator->validate($skus, 'eu-1', false);

        self::assertTrue(
            $validationResult->isValid(),
            'Validation wrongly detected an unknown source code'
        );
    }

    /**
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/sources.php
     */
    public function testNonSenseTransfer()
    {
        $skus = ['SKU-1'];

        $validationResult = $this->bulkInventoryTransferValidator->validate(
            $skus,
            $this->defaultSourceProvider->getCode(),
            true
        );

        $errors = $validationResult->getErrors();

        self::assertFalse(
            $validationResult->isValid(),
            'Validation did not detect non sense transfer'
        );

        self::assertEquals(
            'Cannot transfer default source to itself',
            $errors[0]->getText(),
            'Unexpected error message from validator'
        );
    }
}
