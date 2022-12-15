<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Controller\Adminhtml\Category\Image;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\TestFramework\TestCase\AbstractBackendController;

/**
 * Test cases related to upload category image.
 *
 * @see \Magento\Catalog\Controller\Adminhtml\Category\Image\Upload
 * @magentoAppArea adminhtml
 * @magentoDbIsolation enabled
 */
class UploadTest extends AbstractBackendController
{
    /** @var Filesystem */
    private $filesystem;

    /** @var WriteInterface */
    private $tmpDirectory;

    /** @var SerializerInterface */
    private $json;

    /** @var string */
    private $fileToRemove;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->filesystem = $this->_objectManager->get(Filesystem::class);
        $this->tmpDirectory = $this->filesystem->getDirectoryWrite(DirectoryList::TMP);
        $this->json = $this->_objectManager->get(SerializerInterface::class);
    }

    /**
     * @inheritdoc
     */
    protected function tearDown(): void
    {
        if (file_exists($this->fileToRemove)) {
            unlink($this->fileToRemove);
        }

        parent::tearDown();
    }

    /**
     * @return void
     */
    public function testWithNotAllowedFileExtension(): void
    {
        $this->getRequest()->setMethod(HttpRequest::METHOD_POST);
        $this->getRequest()->setPostValue('param_name', 'image');
        $this->prepareFile('empty.csv');
        $this->dispatch('backend/catalog/category_image/upload');
        $responseBody = $this->json->unserialize($this->getResponse()->getBody());
        $this->assertNotEmpty($responseBody['error']);
        $this->assertStringContainsString((string)__('File validation failed.'), $responseBody['error']);
    }

    /**
     * Prepare file
     *
     * @param string $fileName
     * @return void
     */
    private function prepareFile(string $fileName): void
    {
        $this->tmpDirectory->create($this->tmpDirectory->getAbsolutePath());
        $filePath = $this->tmpDirectory->getAbsolutePath($fileName);
        $this->fileToRemove = $filePath;
        $fixtureDir = realpath(__DIR__ . '/../../../../_files');
        $this->tmpDirectory->getDriver()->copy($fixtureDir . DIRECTORY_SEPARATOR . $fileName, $filePath);
        $_FILES['image'] = [
            'name' => $fileName,
            'type' => 'image',
            'tmp_name' => $filePath,
            'error' => 0,
            'size' => 12500,
        ];
    }
}
