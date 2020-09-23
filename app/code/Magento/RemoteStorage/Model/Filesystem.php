<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\RemoteStorage\Model;

use Magento\Framework\Filesystem\DirectoryList;
use Magento\Framework\Filesystem\Directory\ReadFactory;
use Magento\Framework\Filesystem\Directory\WriteFactory;

/**
 * Filesystem implementation for remote storage.
 */
class Filesystem extends \Magento\Framework\Filesystem
{
    /**
     * @var bool
     */
    private $isEnabled;

    /**
     * @param DirectoryList $directoryList
     * @param ReadFactory $readFactory
     * @param WriteFactory $writeFactory
     * @param Config $config
     */
    public function __construct(
        DirectoryList $directoryList,
        ReadFactory $readFactory,
        WriteFactory $writeFactory,
        Config $config
    ) {
        $this->isEnabled = $config->isEnabled();

        parent::__construct($directoryList, $readFactory, $writeFactory);
    }

    /**
     * Gets URL path by code.
     *
     * @param string $code
     * @return string
     */
    protected function getDirPath($code): string
    {
        if ($this->isEnabled) {
            return $this->directoryList->getUrlPath($code) ?: '/';
        }

        return parent::getDirPath($code);
    }
}
