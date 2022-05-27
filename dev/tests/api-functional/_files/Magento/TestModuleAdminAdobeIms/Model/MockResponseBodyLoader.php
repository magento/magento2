<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\TestModuleAdminAdobeIms\Model;

use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\Module\Dir;
use Magento\Framework\Filesystem\Io\File;

/**
 * Load mock response body
 */
class MockResponseBodyLoader
{
    private const RESPONSE_FILE_PATTERN = '%s/_files/mock_response.json';

    /**
     * @var Dir
     */
    private $moduleDirectory;

    /**
     * @var File
     */
    private $fileIo;

    /**
     * @param Dir $moduleDirectory
     * @param File $fileIo
     */
    public function __construct(
        Dir $moduleDirectory,
        File $fileIo
    ) {
        $this->moduleDirectory = $moduleDirectory;
        $this->fileIo = $fileIo;
    }

    /**
     * Loads mock profile response body
     *
     * @param string $country
     * @return string
     * @throws NotFoundException
     */
    public function loadForRequest()
    {
        $moduleDir = $this->moduleDirectory->getDir('Magento_TestModuleAdminAdobeIms');
        $responsePath = sprintf(static::RESPONSE_FILE_PATTERN, $moduleDir);

        return $this->fileIo->read($responsePath);
    }
}
