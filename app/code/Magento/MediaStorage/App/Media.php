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
use Magento\Catalog\Model\View\Asset\PlaceholderFactory;
use Magento\Framework\App;
use Magento\Framework\App\Area;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\State;
use Magento\Framework\AppInterface;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\MediaStorage\Model\File\Storage\Config;
use Magento\MediaStorage\Model\File\Storage\ConfigFactory;
use Magento\MediaStorage\Model\File\Storage\Response;
use Magento\MediaStorage\Model\File\Storage\Synchronization;
use Magento\MediaStorage\Model\File\Storage\SynchronizationFactory;
use Magento\MediaStorage\Service\ImageResize;

/**
 * Media Storage
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
    private $directory;

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
        ImageResize $imageResize
    ) {
        $this->response = $response;
        $this->isAllowed = $isAllowed;
        $this->directory = $filesystem->getDirectoryWrite(DirectoryList::PUB);
        $mediaDirectory = trim($mediaDirectory);
        if (!empty($mediaDirectory)) {
            // phpcs:ignore Magento2.Functions.DiscouragedFunction
            $this->mediaDirectoryPath = str_replace('\\', '/', realpath($mediaDirectory));
        }
        $this->configCacheFile = $configCacheFile;
        $this->relativeFileName = $relativeFileName;
        $this->configFactory = $configFactory;
        $this->syncFactory = $syncFactory;
        $this->placeholderFactory = $placeholderFactory;
        $this->appState = $state;
        $this->imageResize = $imageResize;
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

        if ($this->mediaDirectoryPath !== $this->directory->getAbsolutePath()) {
            // Path to media directory changed or absent - update the config
            /** @var Config $config */
            $config = $this->configFactory->create(['cacheFile' => $this->configCacheFile]);
            $config->save();
            $this->mediaDirectoryPath = $config->getMediaDirectory();
            $allowedResources = $config->getAllowedResources();
            $isAllowed = $this->isAllowed;
            if (!$isAllowed($this->relativeFileName, $allowedResources)) {
                throw new LogicException('The path is not allowed: ' . $this->relativeFileName);
            }
        }

        try {
            /** @var Synchronization $sync */
            $sync = $this->syncFactory->create(['directory' => $this->directory]);
            $sync->synchronize($this->relativeFileName);
            $this->imageResize->resizeFromImageName($this->getOriginalImage($this->relativeFileName));
            if ($this->directory->isReadable($this->relativeFileName)) {
                $this->response->setFilePath($this->directory->getAbsolutePath($this->relativeFileName));
            } else {
                $this->setPlaceholderImage();
            }
        } catch (Exception $e) {
            $this->setPlaceholderImage();
        }

        return $this->response;
    }

    /**
     * Set Placeholder as a response
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
        return preg_replace('|^.*((?:/[^/]+){3})$|', '$1', $resizedImagePath);
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
