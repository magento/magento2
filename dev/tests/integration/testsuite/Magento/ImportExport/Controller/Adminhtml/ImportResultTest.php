<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ImportExport\Controller\Adminhtml;

use Magento\Framework\Filesystem\DirectoryList;
use Magento\Framework\HTTP\Adapter\FileTransferFactory;
use Magento\ImportExport\Model\Import;
use Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingErrorAggregatorInterface;
use Magento\ImportExport\Controller\Adminhtml\Import\HttpFactoryMock;

/**
 * Test for \Magento\ImportExport\Controller\Adminhtml\ImportResult class.
 *
 * @magentoAppArea adminhtml
 */
class ImportResultTest extends \Magento\TestFramework\TestCase\AbstractBackendController
{
    /**
     * @param string $fileName
     * @param string $mimeType
     * @param string $delimiter
     * @backupGlobals enabled
     * @magentoDbIsolation enabled
     * @dataProvider validationDataProvider
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    public function testAddErrorMessages(string $fileName, string $mimeType, string $delimiter): void
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
        $target = $tmpDir->getAbsolutePath("{$subDir}" . DIRECTORY_SEPARATOR . "{$fileName}");
        copy(__DIR__ . DIRECTORY_SEPARATOR . 'Import' . DIRECTORY_SEPARATOR . '_files'
            . DIRECTORY_SEPARATOR . "{$fileName}", $target);

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
        $this->assertStringNotContainsString('&lt;br&gt;', $this->getResponse()->getBody());
    }

    /**
     * @return array
     */
    public function validationDataProvider(): array
    {
        return [
            [
                'file_name' => 'invalid_catalog_products.csv',
                'mime-type' => 'text/csv',
                'delimiter' => ',',
            ],
        ];
    }
}
