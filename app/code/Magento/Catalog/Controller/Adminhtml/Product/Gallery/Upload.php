<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Catalog\Controller\Adminhtml\Product\Gallery;

use Laminas\Uri\Http as HttpUri;
use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem\Driver\File;
use Magento\MediaStorage\Helper\File\Storage\Database;

/**
 * The product gallery upload controller
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Upload extends \Magento\Backend\App\Action implements HttpPostActionInterface
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    public const ADMIN_RESOURCE = 'Magento_Catalog::products';

    /**
     * @var \Magento\Framework\Controller\Result\RawFactory
     */
    protected $resultRawFactory;

    /**
     * @var array
     */
    private $allowedMimeTypes = [
        'jpg' => 'image/jpg',
        'jpeg' => 'image/jpeg',
        'gif' => 'image/gif',
        'png' => 'image/png'
    ];

    /**
     * @var \Magento\Framework\Image\AdapterFactory
     */
    private $adapterFactory;

    /**
     * @var \Magento\Framework\Filesystem
     */
    private $filesystem;

    /**
     * @var \Magento\Catalog\Model\Product\Media\Config
     */
    private $productMediaConfig;

    /**
     * @var Database
     */
    private $fileStorageDatabase;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Controller\Result\RawFactory $resultRawFactory
     * @param \Magento\Framework\Image\AdapterFactory $adapterFactory
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\Catalog\Model\Product\Media\Config $productMediaConfig
     * @param Database|null $fileStorageDatabase
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Controller\Result\RawFactory $resultRawFactory,
        ?\Magento\Framework\Image\AdapterFactory $adapterFactory = null,
        ?\Magento\Framework\Filesystem $filesystem = null,
        ?\Magento\Catalog\Model\Product\Media\Config $productMediaConfig = null,
        ?Database $fileStorageDatabase = null
    ) {
        parent::__construct($context);
        $this->resultRawFactory = $resultRawFactory;
        $this->adapterFactory = $adapterFactory ?: ObjectManager::getInstance()
            ->get(\Magento\Framework\Image\AdapterFactory::class);
        $this->filesystem = $filesystem ?: ObjectManager::getInstance()
            ->get(\Magento\Framework\Filesystem::class);
        $this->productMediaConfig = $productMediaConfig ?: ObjectManager::getInstance()
            ->get(\Magento\Catalog\Model\Product\Media\Config::class);
        $this->fileStorageDatabase = $fileStorageDatabase ?: ObjectManager::getInstance()
            ->get(Database::class);
    }

    /**
     * Upload image(s) to the product gallery.
     *
     * @return \Magento\Framework\Controller\Result\Raw
     */
    public function execute()
    {
        try {
            $uploader = $this->_objectManager->create(
                \Magento\MediaStorage\Model\File\Uploader::class,
                ['fileId' => 'image']
            );
            $uploader->setAllowedExtensions($this->getAllowedExtensions());
            $imageAdapter = $this->adapterFactory->create();
            $uploader->addValidateCallback('catalog_product_image', $imageAdapter, 'validateUploadFile');
            $uploader->setAllowRenameFiles(true);
            $uploader->setFilesDispersion(true);
            $mediaDirectory = $this->filesystem->getDirectoryRead(DirectoryList::MEDIA);
            $result = $uploader->save(
                $mediaDirectory->getAbsolutePath($this->productMediaConfig->getBaseTmpMediaPath())
            );
            $this->_eventManager->dispatch(
                'catalog_product_gallery_upload_image_after',
                ['result' => $result, 'action' => $this]
            );

            if (is_array($result)) {
                unset($result['tmp_name']);
                unset($result['path']);

                $result['url'] = $this->getTmpPreviewUrl($result['file']);
                $result['file'] = $result['file'] . '.tmp';
            } else {
                $result = ['error' => 'Something went wrong while saving the file(s).'];
            }
        } catch (LocalizedException $e) {
            $result = ['error' => $e->getMessage(), 'errorcode' => $e->getCode()];
        } catch (\Throwable $e) {
            $result = ['error' => 'Something went wrong while saving the file(s).', 'errorcode' => 0];
        }

        /** @var \Magento\Framework\Controller\Result\Raw $response */
        $response = $this->resultRawFactory->create();
        $response->setHeader('Content-type', 'text/plain');
        $response->setContents(json_encode($result));
        return $response;
    }

    /**
     * Build the preview URL of an image that was just written to the temporary media directory.
     *
     * A locally stored file is not yet available on a "Base URL for User Media Files" served by another host
     * (CDN, synced mirror), so it is previewed from the host and scheme of the admin request that uploaded it.
     * A media URL on the request's own host, remote storage, database media storage and IPv6 literal hosts
     * keep the configured media URL.
     *
     * @param string $file
     * @return string
     */
    private function getTmpPreviewUrl(string $file): string
    {
        $tmpMediaUrl = $this->productMediaConfig->getTmpMediaUrl($file);
        $request = $this->getRequest();
        $mediaDriver = $this->filesystem->getDirectoryWrite(DirectoryList::MEDIA)->getDriver();
        if (!$mediaDriver instanceof File
            || !$request instanceof HttpRequest
            || $this->fileStorageDatabase->checkDbUsage()
            // getDistroBaseUrl() splits the host on every colon, which breaks an IPv6 literal such as [::1]:8080
            || str_starts_with((string)$request->getServer('HTTP_HOST'), '[')
        ) {
            return $tmpMediaUrl;
        }

        $requestBaseUrl = $request->getDistroBaseUrl();
        $mediaOrigin = $this->getOrigin($tmpMediaUrl);
        if ($mediaOrigin === null || $mediaOrigin === $this->getOrigin($requestBaseUrl)) {
            return $tmpMediaUrl;
        }

        return $requestBaseUrl
            . $this->filesystem->getUri(DirectoryList::MEDIA) . '/'
            . $this->productMediaConfig->getTmpMediaShortUrl($file);
    }

    /**
     * Get the lower-cased host of a URL with its port, omitting the default port of the URL's scheme.
     *
     * The scheme itself is ignored, so http://example.com and https://example.com are the same origin here.
     *
     * @param string $url
     * @return string|null Null for a URL without a host
     */
    private function getOrigin(string $url): ?string
    {
        $uri = new HttpUri($url);
        $host = $uri->getHost();
        if (!$host) {
            return null;
        }
        $port = (int)$uri->getPort();
        $defaultPort = ['http' => 80, 'https' => 443][(string)$uri->getScheme()] ?? null;

        return strtolower($host) . ($port && $port !== $defaultPort ? ':' . $port : '');
    }

    /**
     * Get the set of allowed file extensions.
     *
     * @return array
     */
    private function getAllowedExtensions()
    {
        return array_keys($this->allowedMimeTypes);
    }
}
