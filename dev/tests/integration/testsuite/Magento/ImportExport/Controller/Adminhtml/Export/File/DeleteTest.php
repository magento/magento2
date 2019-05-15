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
    private $fullDirectoryPath;

    /**
     * @var string
     */
    private $fileName = 'catalog_product.csv';

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        parent::setUp();

        $this->filesystem = $this->_objectManager->get(Filesystem::class);
        $baseDirectory = $this->filesystem->getDirectoryWrite(DirectoryList::ROOT);
        $this->varDirectory = $this->filesystem->getDirectoryWrite(DirectoryList::VAR_DIR);
        $this->varDirectory->create($this->varDirectory->getRelativePath('export'));
        $this->fullDirectoryPath = $this->varDirectory->getAbsolutePath('export');
        $filePath =  $this->fullDirectoryPath . DIRECTORY_SEPARATOR . $this->fileName;
        $fixtureDir = realpath(__DIR__ . '/../../Import/_files');
        $baseDirectory->copyFile($fixtureDir . '/' . $this->fileName, $filePath);
    }

    /**
     * Check that file can be removed under var/export directory.
     *
     * @return void
     * @magentoConfigFixture default_store admin/security/use_form_key 1
     */
    public function testExecute(): void
    {
        $uri = 'backend/admin/export_file/delete/filename/' . $this->fileName;
        $request = $this->getRequest();
        $request->setMethod(Http::METHOD_POST);
        $request->setRequestUri($uri);
        $this->dispatch($uri);

        $this->assertFalse(
            $this->varDirectory->isExist($this->varDirectory->getRelativePath('export/' . $this->fileName))
        );
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
