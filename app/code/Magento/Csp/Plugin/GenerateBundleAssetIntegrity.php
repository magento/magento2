<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Csp\Plugin;

use Magento\Csp\Model\SubresourceIntegrity\HashGenerator;
use Magento\Csp\Model\SubresourceIntegrityCollector;
use Magento\Csp\Model\SubresourceIntegrityFactory;
use Magento\Deploy\Service\Bundle;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Io\File;

class GenerateBundleAssetIntegrity
{
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
     * @var Filesystem
     */
    private Filesystem $filesystem;

    /**
     * @var File
     */
    private File $fileIo;

    /**
     * @param HashGenerator $hashGenerator
     * @param SubresourceIntegrityFactory $integrityFactory
     * @param SubresourceIntegrityCollector $integrityCollector
     * @param Filesystem $filesystem
     * @param File $fileIo
     */
    public function __construct(
        HashGenerator $hashGenerator,
        SubresourceIntegrityFactory $integrityFactory,
        SubresourceIntegrityCollector $integrityCollector,
        Filesystem $filesystem,
        File $fileIo
    ) {
        $this->hashGenerator = $hashGenerator;
        $this->integrityFactory = $integrityFactory;
        $this->integrityCollector = $integrityCollector;
        $this->filesystem = $filesystem;
        $this->fileIo = $fileIo;
    }

    /**
     * Generate SRI hashes for JS files in the bundle directory.
     *
     * @param Bundle $subject
     * @param string|null $result
     * @param string $area
     * @param string $theme
     * @param string $locale
     * @return void
     * @throws FileSystemException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterDeploy(Bundle $subject, ?string $result, string $area, string $theme, string $locale)
    {
        if (PHP_SAPI == 'cli') {
            $pubStaticDir = $this->filesystem->getDirectoryRead(DirectoryList::STATIC_VIEW);
            $files = $pubStaticDir->search(
                $area ."/" . $theme . "/" . $locale . "/" . Bundle::BUNDLE_JS_DIR . "/*.js"
            );
            
            foreach ($files as $file) {
                $bundlePath = $area . '/' . $theme . '/' . $locale .
                    "/" . Bundle::BUNDLE_JS_DIR . '/' . $this->fileIo->getPathInfo($file)['basename'];
                    
                $integrity = $this->integrityFactory->create(
                    [
                        "data" => [
                            'hash' => $this->hashGenerator->generate(
                                $pubStaticDir->readFile($file)
                            ),
                            'path' => $bundlePath
                        ]
                    ]
                );

                $this->integrityCollector->collect($integrity);
            }
        }
    }
}
