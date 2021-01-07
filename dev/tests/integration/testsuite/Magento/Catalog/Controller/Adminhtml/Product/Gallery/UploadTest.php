<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Controller\Adminhtml\Product\Gallery;

use Magento\Catalog\Model\Product\Media\Config;
use Magento\Framework\App\Filesystem\DirectoryList as AppDirectoryList;
use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Framework\Filesystem\DirectoryList;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\TestFramework\TestCase\AbstractBackendController;

/**
 * Provide tests for admin product upload image action.
 *
 * @magentoAppArea adminhtml
 */
class UploadTest extends AbstractBackendController
{
    /**
     * @inheritdoc
     */
    protected $resource = 'Magento_Catalog::products';

    /**
     * @inheritdoc
     */
    protected $uri = 'backend/catalog/product_gallery/upload';

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var Json
     */
    private $serializer;

    /**
     * @var WriteInterface
     */
    private $mediaDirectory;

    /**
     * @var Config
     */
    private $config;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->httpMethod = HttpRequest::METHOD_POST;
        $this->filesystem = $this->_objectManager->get(Filesystem::class);
        $this->serializer = $this->_objectManager->get(Json::class);
        $this->mediaDirectory = $this->filesystem->getDirectoryWrite(AppDirectoryList::MEDIA);
        $this->config = $this->_objectManager->get(Config::class);
    }

    /**
     * Test upload image on admin product page.
     *
     * @dataProvider uploadActionDataProvider
     * @magentoDbIsolation enabled
     * @param array $file
     * @param array $expectation
     * @return void
     */
    public function testUploadAction(array $file, array $expectation): void
    {
        $this->copyFileToSysTmpDir($file);
        $this->getRequest()->setMethod($this->httpMethod);
        $this->dispatch($this->uri);
        $jsonBody = $this->serializer->unserialize($this->getResponse()->getBody());
        $this->assertEquals($jsonBody['name'], $expectation['name']);
        $this->assertEquals($jsonBody['type'], $expectation['type']);
        $this->assertEquals($jsonBody['file'], $expectation['file']);
        $this->assertEquals($jsonBody['url'], $expectation['url']);
        $this->assertArrayNotHasKey('error', $jsonBody);
        $this->assertArrayNotHasKey('errorcode', $jsonBody);
        $this->assertFileExists(
            $this->getFileAbsolutePath($expectation['tmp_media_path'])
        );
    }

    /**
     * @return array
     */
    public function uploadActionDataProvider(): array
    {
        return [
            'upload_image_with_type_jpg' => [
                'file' => [
                    'name' => 'magento_image.jpg',
                    'type' => 'image/jpeg',
                    'current_path' => '/../../../../_files',
                ],
                'expectation' => [
                    'name' => 'magento_image.jpg',
                    'type' => 'image/jpeg',
                    'file' => '/m/a/magento_image.jpg.tmp',
                    'url' => 'http://localhost/media/tmp/catalog/product/m/a/magento_image.jpg',
                    'tmp_media_path' => '/m/a/magento_image.jpg',
                ],
            ],
            'upload_image_with_type_png' => [
                'file' => [
                    'name' => 'product_image.png',
                    'type' => 'image/png',
                    'current_path' => '/../../../../controllers/_files',
                ],
                'expectation' => [
                    'name' => 'product_image.png',
                    'type' => 'image/png',
                    'file' => '/p/r/product_image.png.tmp',
                    'url' => 'http://localhost/media/tmp/catalog/product/p/r/product_image.png',
                    'tmp_media_path' => '/p/r/product_image.png',
                ],
            ],
            'upload_image_with_type_gif' => [
                'file' => [
                    'name' => 'magento_image.gif',
                    'type' => 'image/gif',
                    'current_path' => '/../../../../_files',
                ],
                'expectation' => [
                    'name' => 'magento_image.gif',
                    'type' => 'image/gif',
                    'file' => '/m/a/magento_image.gif.tmp',
                    'url' => 'http://localhost/media/tmp/catalog/product/m/a/magento_image.gif',
                    'tmp_media_path' => '/m/a/magento_image.gif',
                ],
            ],
        ];
    }

    /**
     * Test upload image on admin product page.
     *
     * @dataProvider uploadActionWithErrorsDataProvider
     * @magentoDbIsolation enabled
     * @param array $file
     * @param array $expectation
     * @return void
     */
    public function testUploadActionWithErrors(array $file, array $expectation): void
    {
        if (!empty($file['create_file'])) {
            $this->createFileInSysTmpDir($file['name']);
        } elseif (!empty($file['copy_file'])) {
            $this->copyFileToSysTmpDir($file);
        }

        $this->getRequest()->setMethod($this->httpMethod);
        $this->dispatch($this->uri);
        $jsonBody = $this->serializer->unserialize($this->getResponse()->getBody());
        $this->assertEquals($expectation['message'], $jsonBody['error']);
        $this->assertEquals($expectation['errorcode'], $jsonBody['errorcode']);

        if (!empty($expectation['tmp_media_path'])) {
            $this->assertFileDoesNotExist(
                $this->getFileAbsolutePath($expectation['tmp_media_path'])
            );
        }
    }

    /**
     * @return array
     */
    public function uploadActionWithErrorsDataProvider(): array
    {
        return [
            'upload_image_with_invalid_type' => [
                'file' => [
                    'create_file' => true,
                    'name' => 'invalid_file.txt',
                ],
                'expectation' => [
                    'message' => 'Disallowed file type.',
                    'errorcode' => 0,
                    'tmp_media_path' => '/i/n/invalid_file.txt',
                ],
            ],
            'upload_empty_image' => [
                'file' => [
                    'copy_file' => true,
                    'name' => 'magento_empty.jpg',
                    'type' => 'image/jpeg',
                    'current_path' => '/../../../../_files',
                ],
                'expectation' => [
                    'message' => 'Wrong file size.',
                    'errorcode' => 0,
                    'tmp_media_path' => '/m/a/magento_empty.jpg',
                ],
            ],
            'upload_without_image' => [
                'file' => [],
                'expectation' => [
                    'message' => '$_FILES array is empty',
                    'errorcode' => 0,
                ],
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    protected function tearDown(): void
    {
        $_FILES = [];
        $this->mediaDirectory->delete('tmp');
        parent::tearDown();
    }

    /**
     * Copies file to tmp dir.
     *
     * @param array $file
     * @return void
     */
    private function copyFileToSysTmpDir(array $file): void
    {
        if (!empty($file)) {
            $tmpDirectory = $this->filesystem->getDirectoryWrite(DirectoryList::SYS_TMP);
            $fixtureDir = realpath(__DIR__ . $file['current_path']);
            $filePath = $tmpDirectory->getAbsolutePath($file['name']);
            copy($fixtureDir . DIRECTORY_SEPARATOR . $file['name'], $filePath);

            $_FILES['image'] = [
                'name' => $file['name'],
                'type' => $file['type'],
                'tmp_name' => $filePath,
            ];
        }
    }

    /**
     * Creates txt file with given name and copies to tmp dir.
     *
     * @param string $name
     * @return void
     */
    private function createFileInSysTmpDir(string $name): void
    {
        $tmpDirectory = $this->filesystem->getDirectoryWrite(DirectoryList::SYS_TMP);
        $filePath = $tmpDirectory->getAbsolutePath($name);
        $file = fopen($filePath, "wb");
        fwrite($file, 'some text');

        $_FILES['image'] = [
            'name' => $name,
            'type' => 'text/plain',
            'tmp_name' => $filePath,
        ];
    }

    /**
     * Returns absolute path to file in media tmp dir.
     *
     * @param string $tmpPath
     * @return string
     */
    private function getFileAbsolutePath(string $tmpPath): string
    {
        return $this->mediaDirectory->getAbsolutePath($this->config->getBaseTmpMediaPath() . $tmpPath);
    }
}
