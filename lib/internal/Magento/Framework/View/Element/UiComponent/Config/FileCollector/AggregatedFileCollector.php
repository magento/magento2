<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Element\UiComponent\Config\FileCollector;

use Magento\Framework\Filesystem\Directory\ReadFactory;
use Magento\Framework\View\DesignInterface;
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
     * @var ReadFactory
     */
    protected $readFactory;

    /**
     * Constructor
     *
     * @param CollectorInterface $collectorAggregated
     * @param DesignInterface $design
     * @param ReadFactory $readFactory
     * @param string $searchPattern
     */
    public function __construct(
        CollectorInterface $collectorAggregated,
        DesignInterface $design,
        ReadFactory $readFactory,
        $searchPattern = null
    ) {
        $this->searchPattern = $searchPattern;
        $this->collectorAggregated = $collectorAggregated;
        $this->design = $design;
        $this->readFactory = $readFactory;
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
        foreach ($files as $file) {
            $fullFileName = $file->getFileName();
            $fileDir = dirname($fullFileName);
            $fileName = basename($fullFileName);
            $dirRead = $this->readFactory->create($fileDir);
            $result[$fullFileName] = $dirRead->readFile($fileName);
        }

        return $result;
    }
}
