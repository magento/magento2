<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ImportExport\Controller\Adminhtml\Export\File;

use Magento\Backend\Model\Auth\Session;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\AbstractBackendController;
use Magento\Backend\Model\UrlInterface as BackendUrl;
use Magento\Backend\Model\Auth;
use Magento\TestFramework\Bootstrap as TestBootstrap;

/**
 * Test for \Magento\ImportExport\Controller\Adminhtml\Export\File\Download class.
 */
class DownloadTest extends AbstractBackendController
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
     * @var string
     */
    private $filesize;

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var Auth
     */
    private $auth;

    /**
     * @var BackendUrl
     */
    private $backendUrl;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        parent::setUp();

        $this->filesystem = $this->_objectManager->get(Filesystem::class);
        $this->auth = $this->_objectManager->get(Auth::class);
        $this->backendUrl = $this->_objectManager->get(BackendUrl::class);
        $baseDirectory = $this->filesystem->getDirectoryWrite(DirectoryList::ROOT);
        $this->varDirectory = $this->filesystem->getDirectoryWrite(DirectoryList::VAR_DIR);
        $this->varDirectory->create($this->varDirectory->getRelativePath('export'));
        $this->fullDirectoryPath = $this->varDirectory->getAbsolutePath('export');
        $filePath =  $this->fullDirectoryPath . DIRECTORY_SEPARATOR . $this->fileName;
        $fixtureDir = realpath(__DIR__ . '/../../Import/_files');
        $baseDirectory->copyFile($fixtureDir . '/' . $this->fileName, $filePath);
        $this->filesize = filesize($filePath);
    }

    /**
     * Check that file can be downloaded.
     *
     * @return void
     * @magentoConfigFixture default_store admin/security/use_form_key 1
     * @magentoAppArea adminhtml
     */
    public function testExecute(): void
    {
        $uri = 'backend/admin/export_file/download/filename/' . $this->fileName;
        $this->prepareRequest($uri);

        $this->dispatch($uri);

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
            $this->filesize,
            $contentLength->getFieldValue(),
            'Incorrect response header "content-length"'
        );
    }

    /**
     * Prepares GET request to download file.
     *
     * @param string $uri
     * @return void
     */
    private function prepareRequest(string $uri): void
    {
        $authSession = $this->_objectManager->create(Session::class);
        $authSession->setIsFirstPageAfterLogin(false);
        $this->auth->login(
            TestBootstrap::ADMIN_NAME,
            TestBootstrap::ADMIN_PASSWORD
        );
        $this->auth->setAuthStorage($authSession);

        list($routeName, $controllerName, $actionName) = explode('/', Download::URL);
        $request = $this->getRequest();
        $request->setMethod(Http::METHOD_GET)
            ->setRouteName($routeName)
            ->setControllerName($controllerName)
            ->setActionName($actionName)
            ->setParam(BackendUrl::SECRET_KEY_PARAM_NAME, $this->backendUrl->getSecretKey())
            ->setRequestUri($uri);
        $this->backendUrl->turnOnSecretKey();
    }

    /**
     * @inheritdoc
     */
    protected function tearDown()
    {
        $this->auth = null;

        parent::tearDown();
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
