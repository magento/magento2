<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\TestModuleFedex\Model;

use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\HTTP\AsyncClient\Request;
use Magento\Framework\Module\Dir;
use Magento\Framework\Filesystem\Io\File;
use Magento\Framework\Stdlib\ArrayManager;

/**
 * Load mock response body for Fedex rate request
 */
class MockResponseBodyLoader
{
    private const RESPONSE_FILE_PATTERN = '%s/_files/mock_response_%s_%s.json';
    private const PATH_COUNTRY = 'RequestedShipment/Recipient/Address/CountryCode';
    private const PATH_SERVICE_TYPE = 'RequestedShipment/ServiceType';

    /**
     * @var Dir
     */
    private $moduleDirectory;

    /**
     * @var File
     */
    private $fileIo;

    /**
     * @var ArrayManager
     */
    private $arrayManager;

    /**
     * @param Dir $moduleDirectory
     * @param File $fileIo
     * @param ArrayManager
     */
    public function __construct(
        Dir $moduleDirectory,
        File $fileIo,
        ArrayManager $arrayManager
    ) {
        $this->moduleDirectory = $moduleDirectory;
        $this->fileIo = $fileIo;
        $this->arrayManager = $arrayManager;
    }

    /**
     * Loads mock response xml for a given request
     *
     * @param array $request
     * @return string
     * @throws NotFoundException
     */
    public function loadForRequest(array $request): string
    {
        $moduleDir = $this->moduleDirectory->getDir('Magento_TestModuleFedex');

        $type = strtolower($this->arrayManager->get(static::PATH_SERVICE_TYPE, $request) ?? 'general');
        $country = strtolower($this->arrayManager->get(static::PATH_COUNTRY, $request) ?? '');

        $responsePath = sprintf(static::RESPONSE_FILE_PATTERN, $moduleDir, $type, $country);

        if (!$this->fileIo->fileExists($responsePath)) {
            throw new NotFoundException(
                __('"%1" is not a valid mock response type for country "%2".', $type, $country)
            );
        }

        return $this->fileIo->read($responsePath);
    }
}
