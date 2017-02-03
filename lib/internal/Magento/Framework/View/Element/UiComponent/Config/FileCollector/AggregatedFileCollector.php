<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Element\UiComponent\Config\FileCollector;

use Magento\Framework\Filesystem;
use Magento\Framework\View\DesignInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\View\File\CollectorInterface;
use Magento\Framework\View\Element\UiComponent\Config\FileCollectorInterface;

/**
 * Class AggregatedFileCollector
 */
class AggregatedFileCollector implements FileCollectorInterface
{
    /**
     * Search pattern
     *
     * @var string
     */
    protected $searchPattern;

    /**
     * @var CollectorInterface
     */
    protected $collectorAggregated;

    /**
     * @var DesignInterface
     */
    protected $design;

    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * Constructor
     *
     * @param CollectorInterface $collectorAggregated
     * @param DesignInterface $design
     * @param Filesystem $filesystem
     * @param string $searchPattern
     */
    public function __construct(
        CollectorInterface $collectorAggregated,
        DesignInterface $design,
        Filesystem $filesystem,
        $searchPattern = null
    ) {
        $this->searchPattern = $searchPattern;
        $this->collectorAggregated = $collectorAggregated;
        $this->design = $design;
        $this->filesystem = $filesystem;
    }

    /**
     * Collect files
     *
     * @param string|null $searchPattern
     * @return array
     * @throws \Exception
     */
    public function collectFiles($searchPattern = null)
    {
        $result = [];
        if ($searchPattern === null) {
            $searchPattern = $this->searchPattern;
        }
        if ($searchPattern === null) {
            throw new \Exception('Search pattern cannot be empty.');
        }
        $files = $this->collectorAggregated->getFiles($this->design->getDesignTheme(), $searchPattern);
        $fileReader = $this->filesystem->getDirectoryRead(DirectoryList::ROOT);
        foreach ($files as $file) {
            $filePath = $fileReader->getRelativePath($file->getFilename());
            $result[sprintf('%x', crc32($filePath))] = $fileReader->readFile($filePath);
        }

        return $result;
    }
}
