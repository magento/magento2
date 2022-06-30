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
 *
 * @magentoAppArea adminhtml
 */
class DeleteTest extends AbstractBackendController
{
    /**
     * @var WriteInterface
     */
    protected $varDirectory;

    /**
     * @var string
     */
    private $fileName = 'catalog_product.csv';

    /**
     * @var Filesystem
     */
    private $fileSystem;

    /**
     * @var string
     */
    private $sourceFilePath;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->fileSystem = $this->_objectManager->get(Filesystem::class);
        $this->sourceFilePath = __DIR__ . '/../../Import/_files' . DIRECTORY_SEPARATOR . $this->fileName;
        //Refers to tests 'var' directory
        $this->varDirectory = $this->fileSystem->getDirectoryWrite(DirectoryList::VAR_IMPORT_EXPORT);
    }

    /**
     * Check that file can be removed under var/export directory.
     *
     * @param string $file
     * @dataProvider testExecuteProvider
     * @return void
     * @magentoConfigFixture default_store admin/security/use_form_key 1
     */
    public function testExecute($file): void
    {
        $fullPath = 'export/' . $file;
        $this->copyFile($fullPath);
        $request = $this->getRequest();
        $request->setParam('filename', $file);
        $request->setMethod(Http::METHOD_POST);

        if ($this->varDirectory->isExist($fullPath)) {
            $this->dispatch('backend/admin/export_file/delete');
        } else {
            throw new \AssertionError('Export product file supposed to exist');
        }

        $this->assertFalse($this->varDirectory->isExist($fullPath));
    }

    /**
     * Copy csv file from sourceFilePath to destinationFilePath
     *
     * @param $destinationFilePath
     * @return void
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    protected function copyFile($destinationFilePath): void
    {
        $driver = $this->varDirectory->getDriver();
        $absolutePath = $this->varDirectory->getAbsolutePath($destinationFilePath);

        $driver->createDirectory(dirname($absolutePath));
        $driver->filePutContents($absolutePath, file_get_contents($this->sourceFilePath));
    }

    /**
     * Csv file path for copying from sourceFilePath and for future deleting
     *
     * @return array
     */
    public static function testExecuteProvider(): array
    {
        return [
            ['catalog_product.csv'],
            ['test/catalog_product.csv']
        ];
    }

    /**
     * @inheritdoc
     */
    public static function tearDownAfterClass(): void
    {
        $filesystem = Bootstrap::getObjectManager()->get(Filesystem::class);
        /** @var WriteInterface $directory */
        $directory = $filesystem->getDirectoryWrite(DirectoryList::VAR_IMPORT_EXPORT);
        if ($directory->isExist('export')) {
            $directory->delete('export');
        }
    }
}
