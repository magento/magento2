<?php
/**
 * Copyright 2020 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\MediaGallerySynchronizationApi\Model;

/**
 * File save pool
 */
class ImportFilesComposite implements ImportFilesInterface
{
    /**
     * @var ImportFilesInterface[]
     */
    private $importers;

    /**
     * @param ImportFilesInterface[] $importers
     */
    public function __construct(array $importers)
    {
        ksort($importers);
        $this->importers = $importers;
    }

    /**
     * @inheritdoc
     */
    public function execute(array $paths): void
    {
        foreach ($this->importers as $importer) {
            $importer->execute($paths);
        }
    }
}
