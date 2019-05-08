<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ImportExport\Controller\Adminhtml\Export\File;

use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * Test for \Magento\ImportExport\Controller\Adminhtml\Export\File\Delete class.
 */
class DeleteTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\ImportExport\Controller\Adminhtml\Export\File\Delete
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
     * @var \Magento\Framework\Filesystem
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
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->filesystem = $this->objectManager->get(\Magento\Framework\Filesystem::class);
        $this->varDirectory = $this->filesystem->getDirectoryWrite(DirectoryList::VAR_DIR);
        $this->varDirectory->create($this->varDirectory->getRelativePath('export'));
        $this->fullDirectoryPath = $this->varDirectory->getAbsolutePath('export');
        $filePath =  $this->fullDirectoryPath . DIRECTORY_SEPARATOR . $this->fileName;
        $fixtureDir = realpath(__DIR__ . '/../../Import/_files');
        copy($fixtureDir . '/' . $this->fileName, $filePath);
        $this->model = $this->objectManager->get(\Magento\ImportExport\Controller\Adminhtml\Export\File\Delete::class);
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
            $this->varDirectory->isExist(
                $this->varDirectory->getRelativePath( 'export/' . $this->fileName)
            )
        );
    }

    /**
     * @inheritdoc
     */
    public static function tearDownAfterClass()
    {
        $filesystem = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->get(\Magento\Framework\Filesystem::class);
        /** @var \Magento\Framework\Filesystem\Directory\WriteInterface $directory */
        $directory = $filesystem->getDirectoryWrite(DirectoryList::VAR_DIR);
        if ($directory->isExist('export')) {
            $directory->delete('export');
        }
    }
}
