<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ImportExport\Controller\Adminhtml\Export\File;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\AbstractBackendController;

/**
 * Test for \Magento\ImportExport\Controller\Adminhtml\Export\File\Delete class.
 */
class DeleteTest extends AbstractBackendController
{
    /**
     * @var WriteInterface
     */
    private $varDirectory;

    /**
     * @var string
     */
    private $fileName = 'catalog_product.csv';

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        parent::setUp();

        $filesystem = $this->_objectManager->get(Filesystem::class);
        $sourceFilePath = __DIR__ . '/../../Import/_files' . DIRECTORY_SEPARATOR . $this->fileName;
        $destinationFilePath = 'export' . DIRECTORY_SEPARATOR . $this->fileName;
        //Refers to tests 'var' directory
        $this->varDirectory = $filesystem->getDirectoryRead(DirectoryList::VAR_DIR);
        //Refers to application root directory
        $rootDirectory = $filesystem->getDirectoryWrite(DirectoryList::ROOT);
        $rootDirectory->copyFile($sourceFilePath, $this->varDirectory->getAbsolutePath($destinationFilePath));
    }

    /**
     * Check that file can be removed under var/export directory.
     *
     * @return void
     * @magentoConfigFixture default_store admin/security/use_form_key 1
     */
    public function testExecute(): void
    {
        $request = $this->getRequest();
        $request->setParam('filename', $this->fileName);
        $request->setMethod(Http::METHOD_POST);

        if ($this->varDirectory->isExist('export/' . $this->fileName)) {
            $this->dispatch('backend/admin/export_file/delete');
        } else {
            throw new \AssertionError('Export product file supposed to exist');
        }

        $this->assertFalse($this->varDirectory->isExist('export/' . $this->fileName));
    }

    /**
     * @inheritdoc
     */
    public static function tearDownAfterClass()
    {
        $filesystem = Bootstrap::getObjectManager()->get(Filesystem::class);
        /** @var WriteInterface $directory */
        $directory = $filesystem->getDirectoryWrite(DirectoryList::VAR_DIR);
        if ($directory->isExist('export')) {
            $directory->delete('export');
        }
    }
}
