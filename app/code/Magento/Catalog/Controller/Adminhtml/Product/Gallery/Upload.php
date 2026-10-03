<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Catalog\Controller\Adminhtml\Product\Gallery;

use Laminas\Uri\Exception\ExceptionInterface as UriException;
use Laminas\Uri\Http as HttpUri;
use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Framework\UrlInterface;
use Magento\MediaStorage\Helper\File\Storage\Database;
use Magento\Store\Model\StoreManagerInterface;

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
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Controller\Result\RawFactory $resultRawFactory
     * @param \Magento\Framework\Image\AdapterFactory $adapterFactory
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\Catalog\Model\Product\Media\Config $productMediaConfig
     * @param Database|null $fileStorageDatabase
     * @param StoreManagerInterface|null $storeManager
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Controller\Result\RawFactory $resultRawFactory,
        ?\Magento\Framework\Image\AdapterFactory $adapterFactory = null,
        ?\Magento\Framework\Filesystem $filesystem = null,
        ?\Magento\Catalog\Model\Product\Media\Config $productMediaConfig = null,
        ?Database $fileStorageDatabase = null,
        ?StoreManagerInterface $storeManager = null
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
        $this->storeManager = $storeManager ?: ObjectManager::getInstance()
            ->get(StoreManagerInterface::class);
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
     * A media URL on the request's own host or on the store's base URL host, remote storage, database media
     * storage and requests without a usable host keep the configured media URL.
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
            || !$this->hasUsableRequestHost($request)
        ) {
            return $tmpMediaUrl;
        }

        $requestBaseUrl = $request->getDistroBaseUrl();
        if (!$this->isOnAnotherHost($tmpMediaUrl, $requestBaseUrl)) {
            return $tmpMediaUrl;
        }

        return rtrim($requestBaseUrl . $this->filesystem->getUri(DirectoryList::MEDIA), '/') . '/'
            . $this->productMediaConfig->getTmpMediaShortUrl($file);
    }

    /**
     * Check that the request carries what getDistroBaseUrl() needs to build a real URL.
     *
     * Without SCRIPT_NAME or HTTP_HOST it returns a hardcoded http://localhost/, and it splits the host on every
     * colon, which breaks an IPv6 literal such as [::1]:8080.
     *
     * @param HttpRequest $request
     * @return bool
     */
    private function hasUsableRequestHost(HttpRequest $request): bool
    {
        $httpHost = (string)$request->getServer('HTTP_HOST');

        return $request->getServer('SCRIPT_NAME') !== null
            && $httpHost !== ''
            && !str_starts_with($httpHost, '[');
    }

    /**
     * Check whether the media URL is served by a host other than the admin request's or the store's base URL.
     *
     * @param string $mediaUrl
     * @param string $requestBaseUrl
     * @return bool
     */
    private function isOnAnotherHost(string $mediaUrl, string $requestBaseUrl): bool
    {
        $mediaOrigin = $this->getOrigin($mediaUrl);
        if ($mediaOrigin === null) {
            return false;
        }
        $store = $this->storeManager->getStore();
        $localOrigins = [
            $this->getOrigin($requestBaseUrl),
            $this->getOrigin((string)$store->getBaseUrl(UrlInterface::URL_TYPE_WEB, false)),
            $this->getOrigin((string)$store->getBaseUrl(UrlInterface::URL_TYPE_WEB, true)),
        ];

        return !in_array($mediaOrigin, $localOrigins, true);
    }

    /**
     * Get the lower-cased host of a URL with its port, omitting the default port of the URL's scheme.
     *
     * The scheme itself is ignored, so http://example.com and https://example.com are the same origin here.
     *
     * @param string $url
     * @return string|null Null for a URL without a host or one that cannot be parsed
     */
    private function getOrigin(string $url): ?string
    {
        try {
            $uri = new HttpUri($url);
        } catch (UriException $e) {
            return null;
        }
        $host = $uri->getHost();
        if (!$host) {
            return null;
        }
        $port = (int)$uri->getPort();
        $defaultPort = ['http' => 80, 'https' => 443][strtolower((string)$uri->getScheme())] ?? null;

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
