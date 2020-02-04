<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
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
     * @var AggregatedFileCollectorFactory
     */
    private $fileCollectorFactory;

    /**
     * @var string
     */
    private $scope;

    /**
     * @param AggregatedFileCollectorFactory $fileCollectorFactory
     */
    public function __construct(AggregatedFileCollectorFactory $fileCollectorFactory)
    {
        $this->fileCollectorFactory = $fileCollectorFactory;
    }

    /**
     * @inheritdoc
     */
    public function get($filename, $scope)
    {
        $this->scope = $scope;
        /** @var AggregatedFileCollector $aggregatedFiles */
        $aggregatedFiles = $this->fileCollectorFactory->create();
        return $aggregatedFiles->collectFiles($filename);
    }
}
