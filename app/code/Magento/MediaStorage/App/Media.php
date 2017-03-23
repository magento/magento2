<?php
/**
 * Media application
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\MediaStorage\App;

use Magento\Framework\Filesystem;
use Magento\MediaStorage\Model\File\Storage\ConfigFactory;
use Magento\MediaStorage\Model\File\Storage\Response;
use Magento\Framework\App;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\AppInterface;
use Magento\MediaStorage\Model\File\Storage\SynchronizationFactory;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Media implements AppInterface
{
    /**
     * Authorization function
     *
     * @var \Closure
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
     * @var \Magento\Framework\Filesystem\Directory\WriteInterface
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
     * @param ConfigFactory $configFactory
     * @param SynchronizationFactory $syncFactory
     * @param Response $response
     * @param \Closure $isAllowed
     * @param string $mediaDirectory
     * @param string $configCacheFile
     * @param string $relativeFileName
     * @param Filesystem $filesystem
     */
    public function __construct(
        ConfigFactory $configFactory,
        SynchronizationFactory $syncFactory,
        Response $response,
        \Closure $isAllowed,
        $mediaDirectory,
        $configCacheFile,
        $relativeFileName,
        Filesystem $filesystem
    ) {
        $this->response = $response;
        $this->isAllowed = $isAllowed;
        $this->directory = $filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        $mediaDirectory = trim($mediaDirectory);
        if (!empty($mediaDirectory)) {
            $this->mediaDirectoryPath = str_replace('\\', '/', realpath($mediaDirectory));
        }
        $this->configCacheFile = $configCacheFile;
        $this->relativeFileName = $relativeFileName;
        $this->configFactory = $configFactory;
        $this->syncFactory = $syncFactory;
    }

    /**
     * Run application
     *
     * @return Response
     * @throws \LogicException
     */
    public function launch()
    {
        if ($this->mediaDirectoryPath !== $this->directory->getAbsolutePath()) {
            // Path to media directory changed or absent - update the config
            /** @var \Magento\MediaStorage\Model\File\Storage\Config $config */
            $config = $this->configFactory->create(['cacheFile' => $this->configCacheFile]);
            $config->save();
            $this->mediaDirectoryPath = $config->getMediaDirectory();
            $allowedResources = $config->getAllowedResources();
            $isAllowed = $this->isAllowed;
            if (!$isAllowed($this->relativeFileName, $allowedResources)) {
                throw new \LogicException('The specified path is not allowed.');
            }
        }

        /** @var \Magento\MediaStorage\Model\File\Storage\Synchronization $sync */
        $sync = $this->syncFactory->create(['directory' => $this->directory]);
        $sync->synchronize($this->relativeFileName);

        if ($this->directory->isReadable($this->relativeFileName)) {
            $this->response->setFilePath($this->directory->getAbsolutePath($this->relativeFileName));
        } else {
            $this->response->setHttpResponseCode(404);
        }
        return $this->response;
    }

    /**
     * {@inheritdoc}
     */
    public function catchException(App\Bootstrap $bootstrap, \Exception $exception)
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
