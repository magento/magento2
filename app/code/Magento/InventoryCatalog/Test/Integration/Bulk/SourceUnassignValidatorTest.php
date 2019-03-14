<?php
/**
 * Copyright :copyright: Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalog\Test\Integration\Bulk;

use Magento\InventoryCatalogApi\Model\BulkSourceUnassignValidatorInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

class SourceUnassignValidatorTest extends TestCase
{
    /**
     * @var BulkSourceUnassignValidatorInterface
     */
    private $massUnassignValidator;

    public function setUp()
    {
        parent::setUp();
        $this->massUnassignValidator = Bootstrap::getObjectManager()->get(BulkSourceUnassignValidatorInterface::class);
    }

    /**
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/sources.php
     */
    public function testSourceValidator()
    {
        $skus = ['SKU-1', 'SKU-2'];
        $sources = ['non-existing-source1', 'non-existing-source2'];

        $validationResult = $this->massUnassignValidator->validate($skus, $sources);

        self::assertFalse(
            $validationResult->isValid(),
            'Validation did not detect invalid source codes'
        );

        $errors = $validationResult->getErrors();
        self::assertCount(2, $errors, 'Validation did not find all invalid source codes');

        self::assertEquals(
            'Source %sourceCode does not exist',
            $errors[0]->getText(),
            'Unexpected error message from validator'
        );
        self::assertEquals(
            'Source %sourceCode does not exist',
            $errors[1]->getText(),
            'Unexpected error message from validator'
        );
    }
}
