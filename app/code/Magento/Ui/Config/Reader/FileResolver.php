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
 * @since 2.2.0
 */
class FileResolver implements FileResolverInterface
{
    /**
     * @var AggregatedFileCollectorFactory
     * @since 2.2.0
     */
    private $fileCollectorFactory;

    /**
     * @var string
     * @since 2.2.0
     */
    private $scope;

    /**
     * @param AggregatedFileCollectorFactory $fileCollectorFactory
     * @since 2.2.0
     */
    public function __construct(AggregatedFileCollectorFactory $fileCollectorFactory)
    {
        $this->fileCollectorFactory = $fileCollectorFactory;
    }

    /**
     * @inheritdoc
     * @since 2.2.0
     */
    public function get($filename, $scope)
    {
        $this->scope = $scope;
        /** @var AggregatedFileCollector $aggregatedFiles */
        $aggregatedFiles = $this->fileCollectorFactory->create();
        return $aggregatedFiles->collectFiles($filename);
    }
}
