<?php
/**
 * Media application
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\MediaStorage\App;

use Closure;
use Exception;
use LogicException;
use Magento\Catalog\Model\Config\CatalogMediaConfig;
use Magento\Catalog\Model\View\Asset\PlaceholderFactory;
use Magento\Framework\App;
use Magento\Framework\App\Area;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\State;
use Magento\Framework\AppInterface;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Framework\Filesystem\Driver\File;
use Magento\MediaStorage\Model\File\Storage\Config;
use Magento\MediaStorage\Model\File\Storage\ConfigFactory;
use Magento\MediaStorage\Model\File\Storage\Response;
use Magento\MediaStorage\Model\File\Storage\Synchronization;
use Magento\MediaStorage\Model\File\Storage\SynchronizationFactory;
use Magento\MediaStorage\Service\ImageResize;

/**
 * The class resize original images
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Media implements AppInterface
{
    /**
     * Authorization function
     *
     * @var Closure
     */
    private $isAllowed;

    /**
     * Media directory path
     *
     * @var string
     */
    private $mediaDirectoryPath;

    /**
     * Configuration cache file path
     *
     * @var string
     */
    private $configCacheFile;

    /**
     * Requested file name relative to working directory
     *
     * @var string
     */
    private $relativeFileName;

    /**
     * @var Response
     */
    private $response;

    /**
     * @var WriteInterface
     */
    private $directoryPub;

    /**
     * @var \Magento\Framework\Filesystem\Directory\WriteInterface
     */
    private $directoryMedia;

    /**
     * @var ConfigFactory
     */
    private $configFactory;

    /**
     * @var SynchronizationFactory
     */
    private $syncFactory;

    /**
     * @var PlaceholderFactory
     */
    private $placeholderFactory;

    /**
     * @var State
     */
    private $appState;

    /**
     * @var ImageResize
     */
    private $imageResize;

    /**
     * @var string
     */
    private $mediaUrlFormat;

    /**
     * @param ConfigFactory $configFactory
     * @param SynchronizationFactory $syncFactory
     * @param Response $response
     * @param Closure $isAllowed
     * @param string $mediaDirectory
     * @param string $configCacheFile
     * @param string $relativeFileName
     * @param Filesystem $filesystem
     * @param PlaceholderFactory $placeholderFactory
     * @param State $state
     * @param ImageResize $imageResize
     * @param File $file
     * @param CatalogMediaConfig $catalogMediaConfig
     * @throws \Magento\Framework\Exception\FileSystemException
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        ConfigFactory $configFactory,
        SynchronizationFactory $syncFactory,
        Response $response,
        Closure $isAllowed,
        $mediaDirectory,
        $configCacheFile,
        $relativeFileName,
        Filesystem $filesystem,
        PlaceholderFactory $placeholderFactory,
        State $state,
        ImageResize $imageResize,
        File $file,
        CatalogMediaConfig $catalogMediaConfig = null
    ) {
        $this->response = $response;
        $this->isAllowed = $isAllowed;
        $this->directoryPub = $filesystem->getDirectoryWrite(
            DirectoryList::PUB,
            Filesystem\DriverPool::FILE
        );
        $this->directoryMedia = $filesystem->getDirectoryWrite(
            DirectoryList::MEDIA,
            Filesystem\DriverPool::FILE
        );
        $mediaDirectory = trim($mediaDirectory);
        if (!empty($mediaDirectory)) {
            // phpcs:ignore Magento2.Functions.DiscouragedFunction
            $this->mediaDirectoryPath = str_replace('\\', '/', $file->getRealPath($mediaDirectory));
        }
        $this->configCacheFile = $configCacheFile;
        $this->relativeFileName = $relativeFileName;
        $this->configFactory = $configFactory;
        $this->syncFactory = $syncFactory;
        $this->placeholderFactory = $placeholderFactory;
        $this->appState = $state;
        $this->imageResize = $imageResize;

        $catalogMediaConfig = $catalogMediaConfig ?: App\ObjectManager::getInstance()->get(CatalogMediaConfig::class);
        $this->mediaUrlFormat = $catalogMediaConfig->getMediaUrlFormat();
    }

    /**
     * Run application
     *
     * @return Response
     * @throws LogicException
     */
    public function launch(): ResponseInterface
    {
        $this->appState->setAreaCode(Area::AREA_GLOBAL);

        if ($this->checkMediaDirectoryChanged()) {
            // Path to media directory changed or absent - update the config
            /** @var Config $config */
            $config = $this->configFactory->create(['cacheFile' => $this->configCacheFile]);
            $config->save();
            $this->mediaDirectoryPath = $config->getMediaDirectory();
            $allowedResources = $config->getAllowedResources();
            $isAllowed = $this->isAllowed;
            $fileAbsolutePath = $this->directoryPub->getAbsolutePath($this->relativeFileName);
            $fileRelativePath = str_replace(rtrim($this->mediaDirectoryPath, '/') . '/', '', $fileAbsolutePath);
            if (!$isAllowed($fileRelativePath, $allowedResources)) {
                throw new LogicException('The path is not allowed: ' . $this->relativeFileName);
            }
        }

        try {
            $this->createLocalCopy();

            if ($this->directoryPub->isReadable($this->relativeFileName)) {
                $this->response->setFilePath($this->directoryPub->getAbsolutePath($this->relativeFileName));
            } else {
                $this->setPlaceholderImage();
            }
        } catch (Exception $e) {
            $this->setPlaceholderImage();
        }

        return $this->response;
    }

    /**
     * Create local copy of file and perform resizing if necessary.
     *
     * @throws NotFoundException
     */
    private function createLocalCopy(): void
    {
        $this->syncFactory->create(['directory' => $this->directoryPub])
            ->synchronize($this->relativeFileName);

        if ($this->directoryPub->isReadable($this->relativeFileName)) {
            return;
        }

        if ($this->mediaUrlFormat === CatalogMediaConfig::HASH) {
            $this->imageResize->resizeFromImageName($this->getOriginalImage($this->relativeFileName));
        }
    }

    /**
     * Check if media directory changed
     *
     * @return bool
     */
    private function checkMediaDirectoryChanged(): bool
    {
        return rtrim($this->mediaDirectoryPath, '/') !== rtrim($this->directoryMedia->getAbsolutePath(), '/');
    }

    /**
     * Set placeholder image into response
     *
     * @return void
     */
    private function setPlaceholderImage(): void
    {
        $placeholder = $this->placeholderFactory->create(['type' => 'image']);
        $this->response->setFilePath($placeholder->getPath());
    }

    /**
     * Find the path to the original image of the cache path
     *
     * @param string $resizedImagePath
     * @return string
     */
    private function getOriginalImage(string $resizedImagePath): string
    {
        return preg_replace('|^.*?((?:/([^/])/([^/])/\2\3)?/?[^/]+$)|', '$1', $resizedImagePath);
    }

    /**
     * @inheritdoc
     */
    public function catchException(App\Bootstrap $bootstrap, Exception $exception)
    {
        $this->response->setHttpResponseCode(404);
        if ($bootstrap->isDeveloperMode()) {
            $this->response->setHeader('Content-Type', 'text/plain');
            $this->response->setBody($exception->getMessage() . "\n" . $exception->getTraceAsString());
        }
        $this->response->sendResponse();
        return true;
    }
}
