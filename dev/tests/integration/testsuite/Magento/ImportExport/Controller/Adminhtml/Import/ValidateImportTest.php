<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ImportExport\Controller\Adminhtml\Import;

use Magento\Framework\Filesystem\DirectoryList;
use Magento\Framework\HTTP\Adapter\FileTransferFactory;
use Magento\ImportExport\Model\Import;
use Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingErrorAggregatorInterface;

/**
 * @magentoAppArea adminhtml
 */
class ValidateImportTest extends \Magento\TestFramework\TestCase\AbstractBackendController
{
    /**
     * @dataProvider validationDataProvider
     * @param string $fileName
     * @param string $mimeType
     * @param string $message
     * @param string $delimiter
     * @throws \Magento\Framework\Exception\FileSystemException
     * @backupGlobals enabled
     * @magentoDbIsolation enabled
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    public function testValidationReturn(string $fileName, string $mimeType, string $message, string $delimiter): void
    {
        $validationStrategy = ProcessingErrorAggregatorInterface::VALIDATION_STRATEGY_STOP_ON_ERROR;

        $this->getRequest()->setParam('isAjax', true);
        $this->getRequest()->setMethod('POST');
        $_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';

        /** @var $formKey \Magento\Framework\Data\Form\FormKey */
        $formKey = $this->_objectManager->get(\Magento\Framework\Data\Form\FormKey::class);
        $this->getRequest()->setPostValue('form_key', $formKey->getFormKey());
        $this->getRequest()->setPostValue('entity', 'catalog_product');
        $this->getRequest()->setPostValue('behavior', 'append');
        $this->getRequest()->setPostValue(Import::FIELD_NAME_VALIDATION_STRATEGY, $validationStrategy);
        $this->getRequest()->setPostValue(Import::FIELD_NAME_ALLOWED_ERROR_COUNT, 0);
        $this->getRequest()->setPostValue('_import_field_separator', $delimiter);

        /** @var \Magento\TestFramework\App\Filesystem $filesystem */
        $filesystem = $this->_objectManager->get(\Magento\Framework\Filesystem::class);
        $tmpDir = $filesystem->getDirectoryWrite(DirectoryList::SYS_TMP);
        $subDir = str_replace('\\', '_', __CLASS__);
        $tmpDir->create($subDir);
        $target = $tmpDir->getAbsolutePath("{$subDir}/{$fileName}");
        copy(__DIR__ . "/_files/{$fileName}", $target);

        $_FILES = [
            'import_file' => [
                'name' => $fileName,
                'type' => $mimeType,
                'tmp_name' => $target,
                'error' => 0,
                'size' => filesize($target)
            ]
        ];

        $this->_objectManager->configure(
            [
                'preferences' => [FileTransferFactory::class => HttpFactoryMock::class]
            ]
        );

        $this->dispatch('backend/admin/import/validate');

        $this->assertStringContainsString($message, $this->getResponse()->getBody());
        $this->assertStringNotContainsString('The file was not uploaded.', $this->getResponse()->getBody());
        $this->assertDoesNotMatchRegularExpression(
            '/clear[^\[]*\[[^\]]*(import_file|import_image_archive)[^\]]*\]/m',
            $this->getResponse()->getBody()
        );
    }

    /**
     * @return array
     */
    public static function validationDataProvider(): array
    {
        return [
            [
                'fileName' => 'catalog_product.csv',
                'mimeType' => 'text/csv',
                'message' => 'File is valid',
                'delimiter' => ',',
            ],
            [
                'fileName' => 'test.txt',
                'mimeType' => 'text/csv',
                'message' => 'The file cannot be uploaded.',
                'delimiter' => ',',
            ],
            [
                'fileName' => 'incorrect_catalog_product_comma.csv',
                'mimeType' => 'text/csv',
                'message' => 'Download full report',
                'delimiter' => ',',
            ],
            [
                'fileName' => 'incorrect_catalog_product_semicolon.csv',
                'mimeType' => 'text/csv',
                'message' => 'Download full report',
                'delimiter' => ';',
            ],
            [
                'fileName' => 'invalid_catalog_products.zip',
                'mimeType' => 'application/zip',
                'message' => 'Data validation failed.',
                'delimiter' => ',',
            ],
            [
                'fileName' => 'catalog_product.zip',
                'mimeType' => 'application/zip',
                'message' => 'File is valid',
                'delimiter' => ',',
            ],
        ];
    }
}
