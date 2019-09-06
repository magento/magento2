<?php
/**
 * Media application
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\MediaStorage\App;

use Magento\Catalog\Model\View\Asset\PlaceholderFactory;
use Magento\Framework\App\State;
use Magento\Framework\Filesystem;
use Magento\MediaStorage\Model\File\Storage\Response;
use Magento\Framework\App;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\AppInterface;
use Magento\MediaStorage\Model\File\Storage\SynchronizationFactory;
use Magento\Framework\App\Area;
use Magento\MediaStorage\Model\File\Storage\Config;
use Magento\MediaStorage\Service\ImageResize;
use Magento\Catalog\Model\Product\Media\Config as ProductMediaConfig;
use Magento\Tests\NamingConvention\true\string;

/**
 * Media storage
 */
class Media implements AppInterface
{
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
     * @var \Magento\MediaStorage\Model\File\Storage\Config
     */
    private $config;

    /**
     * @var ProductMediaConfig
     */
    private $productMediaConfig;

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

    /***
     * @var FileSystem;
     */
    private $filesystem;

    /**
     * @param Config                 $config
     * @param SynchronizationFactory $syncFactory
     * @param Response               $response
     * @param string                 $relativeFileName
     * @param Filesystem             $filesystem
     * @param PlaceholderFactory     $placeholderFactory
     * @param ImageResize            $imageResize
     * @param ProductMediaConfig     $productMediaConfig
     */
    public function __construct(
        \Magento\MediaStorage\Model\File\Storage\Config $config,
        SynchronizationFactory $syncFactory,
        Response $response,
        $relativeFileName,
        Filesystem $filesystem,
        PlaceholderFactory $placeholderFactory,
        ImageResize $imageResize,
        ProductMediaConfig $productMediaConfig
    ) {
        $this->config = $config;
        $this->response = $response;
        $this->filesystem = $filesystem;
        $this->relativeFileName = $relativeFileName;
        $this->syncFactory = $syncFactory;
        $this->placeholderFactory = $placeholderFactory;
        $this->imageResize = $imageResize;
        $this->productMediaConfig = $productMediaConfig;
    }

    /**
     * Run application
     *
     * @return Response
     * @throws \LogicException
     */
    public function launch()
    {
        if (!$this->isAllowedResource($this->relativeFileName)) {
            throw new \LogicException('The specified path is not allowed.');
        }

        try {
            /**
             * @var \Magento\MediaStorage\Model\File\Storage\Synchronization $sync
             */
            $directory = $this->filesystem->getDirectoryWrite(DirectoryList::MEDIA);
            $sync = $this->syncFactory->create(['directory' => $directory]);
            $sync->synchronize($this->relativeFileName);

            if (stripos($this->relativeFileName, $this->productMediaConfig->getBaseMediaPathAddition()) === 0) {
                $this->imageResize->resizeFromImageName($this->getOriginalImage($this->relativeFileName));
            }
            if ($directory->isReadable($this->relativeFileName)) {
                $this->response->setFilePath($directory->getAbsolutePath($this->relativeFileName));
            } else {
                $this->setPlaceholderImage();
            }
        } catch (\Exception $e) {
            $this->setPlaceholderImage();
        }

        return $this->response;
    }

    /***
     * Validate resource allowed / filename relative to media dir
     *
     * @param  string $resource
     * @return bool
     */
    private function isAllowedResource($resource)
    {
        $allowedResources = $this->config->getAllowedResources();
        foreach ($allowedResources as $allowedResource) {
            if (0 === stripos($resource, $allowedResource)) {
                return true;
            }
        }
        return false;
    }

    /***
     * set placeholderimage as response
     */
    private function setPlaceholderImage()
    {
        $placeholder = $this->placeholderFactory->create(['type' => 'image']);
        $this->response->setFilePath($placeholder->getPath());
    }

    /**
     * Find the path to the original image of the cache path
     *
     * @param  string $resizedImagePath
     * @return string
     */
    private function getOriginalImage(string $resizedImagePath): string
    {
        return preg_replace('|^.*((?:/[^/]+){3})$|', '$1', $resizedImagePath);
    }

    /**
     * @inheritdoc
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
