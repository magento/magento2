<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ImportExport\Controller\Adminhtml;

use Magento\Framework\Filesystem\DirectoryList;

/**
 * @magentoAppArea adminhtml
 */
class ImportTest extends \Magento\TestFramework\TestCase\AbstractBackendController
{
    public function testGetFilterAction()
    {
        $this->dispatch('backend/admin/import/index');
        $body = $this->getResponse()->getBody();
        $this->assertContains(
            (string)\Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
                'Magento\ImportExport\Helper\Data'
            )->getMaxUploadSizeMessage(),
            $body
        );
    }

    /**
     * @magentoAppArea adminhtml
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     */
    public function testDuplicateCategoryUrl()
    {

        $this->getRequest()->setParam('isAjax', true);
        $this->getRequest()->setMethod('POST');
        $_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';

        $formKey = $this->_objectManager->get('Magento\Framework\Data\Form\FormKey');
        $this->getRequest()->setPostValue('form_key', $formKey->getFormKey());
        $this->getRequest()->setPostValue('entity', 'catalog_product');
        $this->getRequest()->setPostValue('behavior', 'append');
        $this->getRequest()->setPostValue('_import_field_separator', ',');


        $name = 'products_duplicate_category.csv';

        /** @var \Magento\TestFramework\App\Filesystem $filesystem */
        $filesystem = $this->_objectManager->get('Magento\Framework\Filesystem');
        $tmpDir = $filesystem->getDirectoryWrite(DirectoryList::SYS_TMP);
        $subDir = str_replace('\\', '_', __CLASS__);
        $tmpDir->create($subDir);
        $target = $tmpDir->getAbsolutePath("{$subDir}/{$name}");
        copy(__DIR__ . "/_files/{$name}", $target);

        $_FILES = [
            'import_file' => [
                'name' => $name,
                'type' => 'text/csv',
                'tmp_name' => $target,
                'error' => 0,
                'size' => filesize($target)
            ]
        ];

        $this->_objectManager->configure(
            [
                'preferences' => [
                    'Magento\Framework\HTTP\Adapter\FileTransferFactory' =>
                        'Magento\ImportExport\Controller\Adminhtml\Import\HttpFactoryMock'
                ]
            ]
        );

        $this->dispatch('backend/admin/import/validate');

        $validateBody = $this->getResponse()->getBody();
        //$this->clearRequest();

        $postData = [
            'entity' => 'catalog_product',
            'behavior' => 'replace',
            'validation_strategy' => 'validation-skip-errors',
            'allowed_error_count' => '10',
            '_import_field_separator' => ','
        ];

        $this->getRequest()->setPostValue($postData);

        $this->dispatch('backend/admin/import/start');
        $bodyImport = $this->getResponse()->getBody();
       // echo $body;
        //$this->assertContains(
         //   (string)\Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
        //        'Magento\ImportExport\Helper\Data'
        //    )->getMaxUploadSizeMessage(),
         //   $body
        //);
    }
}
