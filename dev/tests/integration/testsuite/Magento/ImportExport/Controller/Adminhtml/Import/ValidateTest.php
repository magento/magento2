<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ImportExport\Controller\Adminhtml\Import;

use Magento\Framework\Filesystem\DirectoryList;
use Magento\ImportExport\Model\Import;
use Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingErrorAggregatorInterface;

/**
 * @magentoAppArea adminhtml
 */
class ValidateTest extends \Magento\TestFramework\TestCase\AbstractBackendController
{
    /**
     * @dataProvider validationDataProvider
     * @param string $fileName
     * @param string $message
     * @backupGlobals enabled
     * @magentoDbIsolation enabled
     */
    public function testValidationReturn($fileName, $message)
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
        $this->getRequest()->setPostValue('_import_field_separator', ',');

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
                'type' => 'text/csv',
                'tmp_name' => $target,
                'error' => 0,
                'size' => filesize($target)
            ]
        ];

        $this->_objectManager->configure(
            [
                'preferences' => [
                    \Magento\Framework\HTTP\Adapter\FileTransferFactory::class =>
                        \Magento\ImportExport\Controller\Adminhtml\Import\HttpFactoryMock::class
                ]
            ]
        );

        $this->dispatch('backend/admin/import/validate');

        $this->assertContains($message, $this->getResponse()->getBody());
        $this->assertNotContains('The file was not uploaded.', $this->getResponse()->getBody());
        $this->assertNotRegExp(
            '/clear[^\[]*\[[^\]]*(import_file|import_image_archive)[^\]]*\]/m',
            $this->getResponse()->getBody()
        );
    }

    /**
     * @return array
     */
    public function validationDataProvider()
    {
        return [
            [
                'file_name' => 'catalog_product.csv',
                'message' => 'File is valid'
            ],
            [
                'file_name' => 'test.txt',
                'message' => '\'txt\' file extension is not supported'
            ]
        ];
    }
}
