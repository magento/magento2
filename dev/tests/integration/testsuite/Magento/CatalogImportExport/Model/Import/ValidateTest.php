<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogImportExport\Model\Import;

use Magento\CatalogImportExport\Model\Import\Product\RowValidatorInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\ImportExport\Model\Import;
use Magento\TestFramework\Helper\Bootstrap;
use Psr\Log\LoggerInterface;

/**
 * Integration test for \Magento\CatalogImportExport\Model\Import\Product class.
 *
 * @magentoAppArea adminhtml
 */
class ValidateTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\CatalogImportExport\Model\Import\Product
     */
    protected $_model;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $logger = $this->getMockBuilder(LoggerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->_model = Bootstrap::getObjectManager()->create(
            \Magento\CatalogImportExport\Model\Import\Product::class,
            ['logger' => $logger]
        );

        parent::setUp();
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/products_with_multiselect_attribute.php
     */
    public function testValidateInvalidMultiselectValues()
    {
        $filesystem = Bootstrap::getObjectManager()->create(
            \Magento\Framework\Filesystem::class
        );
        $directory = $filesystem->getDirectoryWrite(DirectoryList::ROOT);
        $source = Bootstrap::getObjectManager()->create(
            \Magento\ImportExport\Model\Import\Source\Csv::class,
            [
                'file' => __DIR__ . '/_files/products_with_invalid_multiselect_values.csv',
                'directory' => $directory
            ]
        );
        $errors = $this->_model->setParameters(
            ['behavior' => \Magento\ImportExport\Model\Import::BEHAVIOR_APPEND, 'entity' => 'catalog_product']
        )->setSource(
            $source
        )->validateData();

        $this->assertTrue($errors->getErrorsCount() == 1);
        $this->assertEquals(
            "Value for 'multiselect_attribute' attribute contains incorrect value, "
            . "see acceptable values on settings specified for Admin",
            $errors->getErrorByRowNumber(1)[0]->getErrorMessage()
        );
    }

    /**
     * @magentoDataFixture Magento/Catalog/Model/ResourceModel/_files/product_simple.php
     * @dataProvider validateUrlKeysDataProvider
     * @param $importFile string
     * @param $expectedErrors array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function testValidateUrlKeys($importFile, $expectedErrors)
    {
        $filesystem = Bootstrap::getObjectManager()->create(
            \Magento\Framework\Filesystem::class
        );
        $directory = $filesystem->getDirectoryWrite(DirectoryList::ROOT);

        $source = Bootstrap::getObjectManager()->create(
            \Magento\ImportExport\Model\Import\Source\Csv::class,
            [
                'file' => __DIR__ . '/_files/' . $importFile,
                'directory' => $directory
            ]
        );
        /** @var \Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingErrorAggregatorInterface $errors */
        $errors = $this->_model->setParameters(
            ['behavior' => \Magento\ImportExport\Model\Import::BEHAVIOR_APPEND, 'entity' => 'catalog_product']
        )->setSource(
            $source
        )->validateData();
        $this->assertEquals(
            $expectedErrors[RowValidatorInterface::ERROR_DUPLICATE_URL_KEY],
            count($errors->getErrorsByCode([RowValidatorInterface::ERROR_DUPLICATE_URL_KEY]))
        );
    }

    /**
     * @return array
     */
    public function validateUrlKeysDataProvider()
    {
        return [
            [
                'products_to_check_valid_url_keys.csv',
                 [
                     RowValidatorInterface::ERROR_DUPLICATE_URL_KEY => 0
                 ]
            ],
            [
                'products_to_check_duplicated_url_keys.csv',
                [
                    RowValidatorInterface::ERROR_DUPLICATE_URL_KEY => 2
                ]
            ],
            [
                'products_to_check_duplicated_names.csv' ,
                [
                    RowValidatorInterface::ERROR_DUPLICATE_URL_KEY => 1
                ]
            ]
        ];
    }

    /**
     * @magentoDataFixture Magento/Store/_files/website.php
     * @magentoDataFixture Magento/Store/_files/core_fixturestore.php
     * @magentoDataFixture Magento/Catalog/Model/ResourceModel/_files/product_simple.php
     */
    public function testValidateUrlKeysMultipleStores()
    {
        $filesystem = Bootstrap::getObjectManager()->create(
            \Magento\Framework\Filesystem::class
        );
        $directory = $filesystem->getDirectoryWrite(DirectoryList::ROOT);

        $source = Bootstrap::getObjectManager()->create(
            \Magento\ImportExport\Model\Import\Source\Csv::class,
            [
                'file' => __DIR__ . '/_files/products_to_check_valid_url_keys_multiple_stores.csv',
                'directory' => $directory
            ]
        );
        $errors = $this->_model->setParameters(
            ['behavior' => \Magento\ImportExport\Model\Import::BEHAVIOR_APPEND, 'entity' => 'catalog_product']
        )->setSource(
            $source
        )->validateData();

        $this->assertTrue($errors->getErrorsCount() == 0);
    }

    /**
     * @param array $row
     * @param string|null $behavior
     * @param bool $expectedResult
     * @magentoDataFixture Magento/Catalog/Model/ResourceModel/_files/product_simple.php
     * @dataProvider validateRowDataProvider
     */
    public function testValidateRow(array $row, $behavior, $expectedResult)
    {
        $this->_model->setParameters(['behavior' => $behavior, 'entity' => 'catalog_product']);
        $this->assertSame($expectedResult, $this->_model->validateRow($row, 1));
    }

    /**
     * @return array
     */
    public function validateRowDataProvider()
    {
        return [
            [
                'row' => ['sku' => 'simple products'],
                'behavior' => null,
                'expectedResult' => true,
            ],
            [
                'row' => ['sku' => 'simple products absent'],
                'behavior' => null,
                'expectedResult' => false,
            ],
            [
                'row' => [
                    'sku' => 'simple products absent',
                    'name' => 'Test',
                    'product_type' => 'simple',
                    '_attribute_set' => 'Default',
                    'price' => 10.20,
                ],
                'behavior' => null,
                'expectedResult' => true,
            ],
            [
                'row' => ['sku' => 'simple products'],
                'behavior' => Import::BEHAVIOR_ADD_UPDATE,
                'expectedResult' => true,
            ],
            [
                'row' => ['sku' => 'simple products absent'],
                'behavior' => Import::BEHAVIOR_ADD_UPDATE,
                'expectedResult' => false,
            ],
            [
                'row' => [
                    'sku' => 'simple products absent',
                    'name' => 'Test',
                    'product_type' => 'simple',
                    '_attribute_set' => 'Default',
                    'price' => 10.20,
                ],
                'behavior' => Import::BEHAVIOR_ADD_UPDATE,
                'expectedResult' => true,
            ],
            [
                'row' => ['sku' => 'simple products'],
                'behavior' => Import::BEHAVIOR_DELETE,
                'expectedResult' => true,
            ],
            [
                'row' => ['sku' => 'simple products absent'],
                'behavior' => Import::BEHAVIOR_DELETE,
                'expectedResult' => false,
            ],
            [
                'row' => ['sku' => 'simple products'],
                'behavior' => Import::BEHAVIOR_REPLACE,
                'expectedResult' => false,
            ],
            [
                'row' => ['sku' => 'simple products absent'],
                'behavior' => Import::BEHAVIOR_REPLACE,
                'expectedResult' => false,
            ],
            [
                'row' => [
                    'sku' => 'simple products absent',
                    'name' => 'Test',
                    'product_type' => 'simple',
                    '_attribute_set' => 'Default',
                    'price' => 10.20,
                ],
                'behavior' => Import::BEHAVIOR_REPLACE,
                'expectedResult' => false,
            ],
            [
                'row' => [
                    'sku' => 'simple products',
                    'name' => 'Test',
                    'product_type' => 'simple',
                    '_attribute_set' => 'Default',
                    'price' => 10.20,
                ],
                'behavior' => Import::BEHAVIOR_REPLACE,
                'expectedResult' => true,
            ],
            [
                'row' => ['sku' => 'sku with whitespace ',
                    'name' => 'Test',
                    'product_type' => 'simple',
                    '_attribute_set' => 'Default',
                    'price' => 10.20,
                ],
                'behavior' => Import::BEHAVIOR_ADD_UPDATE,
                'expectedResult' => false,
            ],
        ];
    }

    /**
     * @covers \Magento\ImportExport\Model\Import\Entity\AbstractEntity::_saveValidatedBunches
     */
    public function testValidateData()
    {
        $filesystem = Bootstrap::getObjectManager()
            ->create(\Magento\Framework\Filesystem::class);
        $directory = $filesystem->getDirectoryWrite(DirectoryList::ROOT);

        $source = Bootstrap::getObjectManager()->create(
            \Magento\ImportExport\Model\Import\Source\Csv::class,
            [
                'file' => __DIR__ . '/_files/products_to_import.csv',
                'directory' => $directory
            ]
        );

        $errors = $this->_model->setParameters(
            ['behavior' => \Magento\ImportExport\Model\Import::BEHAVIOR_APPEND, 'entity' => 'catalog_product']
        )->setSource($source)->validateData();

        $this->assertTrue($errors->getErrorsCount() == 0);
    }
}
