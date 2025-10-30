<?php
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Csp\Plugin;

use Magento\Framework\View\Asset\File;
use Magento\RequireJs\Model\FileManager;
use Magento\Csp\Model\SubresourceIntegrityFactory;
use Magento\Csp\Model\SubresourceIntegrityCollector;
use Magento\Csp\Model\SubresourceIntegrity\HashGenerator;

/**
 * Plugin to add asset integrity value after static content deploy.
 */
class GenerateAssetIntegrity
{
    /**
     * Supported content types.
     *
     * @var array
     */
    private const CONTENT_TYPES = ["js"];

    /**
     * @var HashGenerator
     */
    private HashGenerator $hashGenerator;

    /**
     * @var SubresourceIntegrityFactory
     */
    private SubresourceIntegrityFactory $integrityFactory;

    /**
     * @var SubresourceIntegrityCollector
     */
    private SubresourceIntegrityCollector $integrityCollector;

    /**
     * @param HashGenerator $hashGenerator
     * @param SubresourceIntegrityFactory $integrityFactory
     * @param SubresourceIntegrityCollector $integrityCollector
     */
    public function __construct(
        HashGenerator $hashGenerator,
        SubresourceIntegrityFactory $integrityFactory,
        SubresourceIntegrityCollector $integrityCollector
    ) {
        $this->hashGenerator = $hashGenerator;
        $this->integrityFactory = $integrityFactory;
        $this->integrityCollector = $integrityCollector;
    }

    /**
     * Generates integrity for RequireJs config.
     *
     * @param FileManager $subject
     * @param File $result
     *
     * @return File
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterCreateRequireJsConfigAsset(
        FileManager $subject,
        File $result
    ): File {
        if (PHP_SAPI == 'cli') {
            if (in_array($result->getContentType(), self::CONTENT_TYPES)) {
                $integrity = $this->integrityFactory->create(
                    [
                        "data" => [
                            'hash' => $this->hashGenerator->generate(
                                $result->getContent()
                            ),
                            'path' => $result->getPath()
                        ]
                    ]
                );

                $this->integrityCollector->collect($integrity);
            }
        }

        return $result;
    }

    /**
     * Generates integrity for RequireJs mixins asset.
     *
     * @param FileManager $subject
     * @param File $result
     *
     * @return File
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterCreateRequireJsMixinsAsset(
        FileManager $subject,
        File $result
    ): File {
        if (PHP_SAPI === 'cli') {
            $this->generateHash($result);
        }

        return $result;
    }

    /**
     * Generates integrity for static JS asset.
     *
     * @param FileManager $subject
     * @param File|false $result
     *
     * @return File|false
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterCreateStaticJsAsset(
        FileManager $subject,
        mixed $result
    ): mixed {
        if ($result !== false && PHP_SAPI === 'cli') {
            $this->generateHash($result);
        }

        return $result;
    }

    /**
     * Generates hash for the given file result if it matches the supported content types.
     *
     * @param File $result
     * @return void
     */
    private function generateHash(File $result): void
    {
        if (in_array($result->getContentType(), self::CONTENT_TYPES)) {
            try {
                $content = $result->getContent();
            } catch (\Exception $e) {
                $content = null;
            }
            $path = $result->getPath();

            if ($content !== null) {
                $integrity = $this->integrityFactory->create(
                    [
                        "data" => [
                            'hash' => $this->hashGenerator->generate($content),
                            'path' => $path
                        ]
                    ]
                );
                $this->integrityCollector->collect($integrity);
            }
        }
    }
}
