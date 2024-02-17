<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Csp\Plugin;

use Magento\Framework\App\Request\Http;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\View\Asset\AssetInterface;
use Magento\Framework\View\Asset\GroupedCollection;
use Magento\Framework\View\Asset\LocalInterface;
use Magento\Csp\Model\SubresourceIntegrityRepository;
use Magento\Csp\Model\SubresourceIntegrity\HashGenerator;
use Magento\Csp\Model\SubresourceIntegrity\FileUtility;
use Magento\Csp\Model\SubresourceIntegrity;
use Magento\Framework\App\View\Asset\Publisher;
use Magento\RequireJs\Model\FileManager;
use Magento\Framework\View\Asset\File;

/**
 * Plugin to add integrity to assets on page load
 */
class AddAssetIntegrity
{
    /**
     * Expected asset content type.
     *
     * @var string
     */
    private const CONTENT_TYPE = 'js';

    /**
     * @var Http
     */
    private Http $request;

    /**
     * @var SubresourceIntegrityRepository
     */
    private SubresourceIntegrityRepository $integrityRepository;

    /**
     * @var array $controllerActions
     */
    private array $controllerActions;

    private HashGenerator $hashGenerator;

    /**
     * @var FileUtility $fileUtility
     */
    private FileUtility $fileUtility;

    /**
     * @param Http $request
     * @param SubresourceIntegrityRepository $integrityRepository
     * @param HashGenerator $hashGenerator
     * @param FileUtility $fileUtility
     * @param array $controllerActions
     */
    public function __construct(
        Http $request,
        SubresourceIntegrityRepository $integrityRepository,
        HashGenerator $hashGenerator,
        FileUtility $fileUtility,
        array $controllerActions = []
    ) {
        $this->request = $request;
        $this->integrityRepository = $integrityRepository;
        $this->hashGenerator = $hashGenerator;
        $this->fileUtility = $fileUtility;
        $this->controllerActions = $controllerActions;
    }

    /**
     * Before Plugin to add Properties to JS assets
     *
     * @param GroupedCollection $subject
     * @param AssetInterface $asset
     * @param array $properties
     *
     * @return array
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @throws FileSystemException
     */
    public function beforeGetFilteredProperties(
        GroupedCollection $subject,
        AssetInterface $asset,
        array $properties = []
    ): array {
        if ($asset instanceof LocalInterface) {
            if (in_array($this->request->getFullActionName(), $this->controllerActions)) {
                if ($asset->getContentType() === self::CONTENT_TYPE) {
                    $integrity = $this->getIntegrity($asset);
                    if ($integrity->getHash()) {
                        $properties['attributes']['integrity'] = $integrity->getHash();
                        $properties['attributes']['crossorigin'] = 'anonymous';
                    }
                }
            }
        }

        return [$asset, $properties];
    }

    /**
     * After plugin for requirejs-config.js
     *
     * @param FileManager $subject
     * @param File $result
     * @return File
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @throws FileSystemException
     */
    public function afterCreateRequireJsConfigAsset(
        FileManager $subject,
        File $result
    ): File
    {
        $this->getIntegrity($result);
        return $result;
    }

    /**
     * Calculate integrity hash for JS Assets after static content deployment
     *
     * @param Publisher $subject
     * @param bool $result
     * @param AssetInterface $asset
     * @return bool
     * @throws FileSystemException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterPublish(
        Publisher $subject,
        bool $result,
        AssetInterface $asset
    ): bool
    {
        $this->getIntegrity($asset);
        return $result;
    }

    /**
     * Gets Hash value of file content
     *
     * @param AssetInterface $asset
     * @return SubresourceIntegrity
     * @throws FileSystemException
     */
    private function getIntegrity(AssetInterface $asset): SubresourceIntegrity
    {
        $url = $asset->getUrl();
        $integrity = $this->integrityRepository->getByUrl($url);
        $fileContent = $this->fileUtility->getFileContents($asset);
        $calculatedHash = $fileContent ? $this->hashGenerator->generate($fileContent) : '';

        if (!$integrity) {
            $data = new SubresourceIntegrity(
                [
                    'hash' => $calculatedHash,
                    'url' => $url
                ]
            );
            $this->integrityRepository->save($data);
            return $data;
        }

        $hash = $integrity->getHash();
        //logic to determine if cache needs to be updated
        if ($calculatedHash && $hash !== $calculatedHash) {
            $this->integrityRepository->deleteByUrl($asset->getUrl());
            $integrity->setData("hash", $calculatedHash);
            $this->integrityRepository->save($integrity);
        }
       return $integrity;
    }
}
