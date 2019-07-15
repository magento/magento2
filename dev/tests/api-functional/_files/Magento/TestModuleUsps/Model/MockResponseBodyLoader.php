<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\TestModuleUsps\Model;

use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\HTTP\AsyncClient\Request;
use Magento\Framework\Module\Dir;
use Magento\Framework\Filesystem\Io\File;

/**
 * Load mock response body for USPS rate request
 */
class MockResponseBodyLoader
{
    private const RESPONSE_FILE_PATTERN = '%s/_files/mock_response_%s.xml';

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
     * Loads mock response xml for a given request
     *
     * @param Request $request
     * @return string
     * @throws NotFoundException
     */
    public function loadForRequest(Request $request): string
    {
        $moduleDir = $this->moduleDirectory->getDir('Magento_TestModuleUsps');

        $destination = 'us';
        if (strpos($request->getUrl(), 'IntlRateV2Request') !== false) {
            $destination = 'ca';
        }

        $responsePath = sprintf(static::RESPONSE_FILE_PATTERN, $moduleDir, $destination);

        if (!$this->fileIo->fileExists($responsePath)) {
            throw new NotFoundException(__('%1 is not a valid destination country.', $destination));
        }

        return $this->fileIo->read($responsePath);
    }
}
