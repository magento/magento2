<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\TestModuleUps\Model;

use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\Module\Dir;
use Magento\Framework\Filesystem\Io\File;

/**
 * Load mock response body for UPS rate request
 */
class MockResponseBodyLoader
{
    private const RESPONSE_FILE_PATTERN = '%s/_files/mock_response_%s.txt';

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
     * Loads mock cgi response body for a given country
     *
     * @param string $country
     * @return string
     * @throws NotFoundException
     */
    public function loadForRequest(string $country): string
    {
        $country = strtolower($country);
        $moduleDir = $this->moduleDirectory->getDir('Magento_TestModuleUps');

        $responsePath = sprintf(static::RESPONSE_FILE_PATTERN, $moduleDir, $country);

        if (!$this->fileIo->fileExists($responsePath)) {
            throw new NotFoundException(__('%1 is not a valid destination country.', $country));
        }

        return $this->fileIo->read($responsePath);
    }
}
