<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Interception;

use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * Interception config loader per scope
 */
class ConfigLoader implements ConfigLoaderInterface
{
    /** @var DirectoryList */
    private $directoryList;

    /**
     * @param DirectoryList $directoryList
     */
    public function __construct(
        DirectoryList $directoryList
    ) {
        $this->directoryList = $directoryList;
    }

    /**
     * @inheritDoc
     */
    public function load($cacheId)
    {
        $file = $this->directoryList->getPath(DirectoryList::GENERATED_METADATA) . '/' . $cacheId . '.' . 'php';
        if (file_exists($file)) {
            return include $file;
        }

        return [];
    }
}
