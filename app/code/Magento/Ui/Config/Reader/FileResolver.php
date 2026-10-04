<?php
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
namespace Magento\Ui\Config\Reader;

use Magento\Framework\Config\FileResolverInterface;
use Magento\Framework\View\Element\UiComponent\Config\FileCollector\AggregatedFileCollector;
use Magento\Framework\View\Element\UiComponent\Config\FileCollector\AggregatedFileCollectorFactory;

/**
 * UI Component configuration files resolver
 */
class FileResolver implements FileResolverInterface
{

    /**
     * @param AggregatedFileCollectorFactory $fileCollectorFactory
     */
    public function __construct(private readonly AggregatedFileCollectorFactory $fileCollectorFactory)
    {
    }

    /**
     * @inheritdoc
     */
    public function get($filename, $scope)
    {
        /** @var AggregatedFileCollector $aggregatedFiles */
        $aggregatedFiles = $this->fileCollectorFactory->create();
        return $aggregatedFiles->collectFiles($filename);
    }
}
