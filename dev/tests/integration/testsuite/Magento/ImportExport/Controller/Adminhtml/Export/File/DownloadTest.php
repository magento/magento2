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
use Magento\Backend\Model\UrlInterface as BackendUrl;
use Magento\Backend\Model\Auth;

/**
 * Test for \Magento\ImportExport\Controller\Adminhtml\Export\File\Download class.
 */
class DownloadTest extends AbstractBackendController
{
    /**
     * @var string
     */
    private $fileName = 'catalog_product.csv';

    /**
     * @var Auth
     */
    private $auth;

    /**
     * @var BackendUrl
     */
    private $backendUrl;

    /**
     * @var WriteInterface
     */
    private $varDirectory;

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
        $auth = $this->_objectManager->get(Auth::class);
        $auth->getAuthStorage()->setIsFirstPageAfterLogin(false);
        $this->backendUrl = $this->_objectManager->get(BackendUrl::class);
        $this->backendUrl->turnOnSecretKey();
        $this->sourceFilePath = __DIR__ . '/../../Import/_files' . DIRECTORY_SEPARATOR . $this->fileName;
        //Refers to tests 'var' directory
        $this->varDirectory = $this->fileSystem->getDirectoryWrite(DirectoryList::VAR_IMPORT_EXPORT);
    }

    /**
     * Check that file can be downloaded.
     *
     * @param string $file
     * @dataProvider testExecuteProvider
     * @return void
     * @magentoConfigFixture default_store admin/security/use_form_key 1
     * @magentoAppArea adminhtml
     */
    public function testExecute($file): void
    {
        $this->copyFile('export/' . $file);
        $fileSize = $this->varDirectory->stat('export/' . $file)['size'];
        $request = $this->getRequest();
        list($routeName, $controllerName, $actionName) = explode('/', Download::URL);
        $request->setMethod(Http::METHOD_GET)
            ->setRouteName($routeName)
            ->setControllerName($controllerName)
            ->setActionName($actionName);
        $request->setParam('filename', $this->fileName);
        $request->setParam(BackendUrl::SECRET_KEY_PARAM_NAME, $this->backendUrl->getSecretKey());

        ob_start();
        $this->dispatch('backend/admin/export_file/download');
        ob_end_clean();

        $contentType = $this->getResponse()->getHeader('content-type');
        $contentLength = $this->getResponse()->getHeader('content-length');
        $contentDisposition = $this->getResponse()->getHeader('content-disposition');

        $this->assertEquals(200, $this->getResponse()->getStatusCode(), 'Incorrect response status code');
        $this->assertEquals(
            'application/octet-stream',
            $contentType->getFieldValue(),
            'Incorrect response header "content-type"'
        );
        $this->assertEquals(
            'attachment; filename="export/' . $this->fileName . '"',
            $contentDisposition->getFieldValue(),
            'Incorrect response header "content-disposition"'
        );
        $this->assertEquals(
            $fileSize,
            $contentLength->getFieldValue(),
            'Incorrect response header "content-length"'
        );
    }

    /**
     * Copy csv file from sourceFilePath to destinationFilePath
     *
     * @param $destinationFilePath
     * @return void
     */
    private function copyFile($destinationFilePath): void
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
    protected function tearDown(): void
    {
        $this->auth = null;

        parent::tearDown();
    }

    /**
     * @inheritdoc
     */
    public static function tearDownAfterClass(): void
    {
        $filesystem = Bootstrap::getObjectManager()->get(Filesystem::class);
        /** @var WriteInterface $directory */
        $directory = $filesystem->getDirectoryWrite(DirectoryList::VAR_DIR);
        if ($directory->isExist('export')) {
            $directory->delete('export');
        }
    }
}
