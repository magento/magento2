<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ImportExport\Controller\Adminhtml\Export\File;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\ImportExport\Controller\Adminhtml\Export\File\Delete;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * Test for \Magento\ImportExport\Controller\Adminhtml\Export\File\Delete class.
 */
class DeleteTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Delete
     */
    private $model;

    /**
     * @var \Magento\Framework\Filesystem\Directory\WriteInterface
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
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->filesystem = $this->objectManager->get(Filesystem::class);
        $this->varDirectory = $this->filesystem->getDirectoryWrite(DirectoryList::VAR_DIR);
        $this->varDirectory->create($this->varDirectory->getRelativePath('export'));
        $this->fullDirectoryPath = $this->varDirectory->getAbsolutePath('export');
        $filePath =  $this->fullDirectoryPath . DIRECTORY_SEPARATOR . $this->fileName;
        $fixtureDir = realpath(__DIR__ . '/../../Import/_files');
        copy($fixtureDir . '/' . $this->fileName, $filePath);
        $this->model = $this->objectManager->get(Delete::class);
    }

    /**
     * Check that file can be removed under var/export directory.
     *
     * @return void
     */
    public function testExecute()
    {
        $this->model->getRequest()->setMethod('GET')->setParams(['filename' => 'catalog_product.csv']);
        $this->model->execute();

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
        /** @var \Magento\Framework\Filesystem\Directory\WriteInterface $directory */
        $directory = $filesystem->getDirectoryWrite(DirectoryList::VAR_DIR);
        if ($directory->isExist('export')) {
            $directory->delete('export');
        }
    }
}
