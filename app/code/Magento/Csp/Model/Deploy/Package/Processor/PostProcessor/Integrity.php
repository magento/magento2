<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Csp\Model\Deploy\Package\Processor\PostProcessor;

use Magento\Framework\Filesystem;
use Magento\Deploy\Package\Package;
use Magento\Csp\Model\SubresourceIntegrityFactory;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Csp\Model\SubresourceIntegrityCollector;
use Magento\Deploy\Package\Processor\ProcessorInterface;
use Magento\Csp\Model\SubresourceIntegrity\HashGenerator;

/**
 * Post-processor that generates integrity hashes after static content package deployed.
 */
class Integrity implements ProcessorInterface
{
    /**
     * @var Filesystem
     */
    private Filesystem $filesystem;

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
     * @param Filesystem $filesystem
     * @param HashGenerator $hashGenerator
     * @param SubresourceIntegrityFactory $integrityFactory
     * @param SubresourceIntegrityCollector $integrityCollector
     */
    public function __construct(
        Filesystem $filesystem,
        HashGenerator $hashGenerator,
        SubresourceIntegrityFactory $integrityFactory,
        SubresourceIntegrityCollector $integrityCollector
    ) {
        $this->filesystem = $filesystem;
        $this->hashGenerator = $hashGenerator;
        $this->integrityFactory = $integrityFactory;
        $this->integrityCollector = $integrityCollector;
    }

    /**
     * @inheritdoc
     */
    public function process(Package $package, array $options): bool
    {
        $staticDir = $this->filesystem->getDirectoryRead(
            DirectoryList::ROOT
        );

        foreach ($package->getFiles() as $file) {
            if ($file->getExtension() == "js") {
                $integrity = $this->integrityFactory->create(
                    [
                        "data" => [
                            'hash' => $this->hashGenerator->generate(
                                $staticDir->readFile($file->getSourcePath())
                            ),
                            'path' => $file->getDeployedFilePath()
                        ]
                    ]
                );

                $this->integrityCollector->collect($integrity);
            }
        }

        return true;
    }
}
