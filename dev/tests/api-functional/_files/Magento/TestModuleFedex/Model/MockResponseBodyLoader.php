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
    private const REST_RESPONSE_FILE_PATTERN = '%s/_files/mock_rest_response_%s_%s.json';
    private const REST_PATH_COUNTRY = 'requestedShipment/recipient/address/countryCode';
    private const REST_PATH_SERVICE_TYPE = 'requestedShipment/serviceType';
    private const REST_AUTH_RESPONSE_FILE = '%s/_files/mock_rest_response_auth.json';

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
     * Loads mock json response for a given request
     *
     * @param array $request
     * @return string
     * @throws NotFoundException
     */
    public function loadForRestRequest(array $request): string
    {
        $moduleDir = $this->moduleDirectory->getDir('Magento_TestModuleFedex');

        $type = strtolower($this->arrayManager->get(static::REST_PATH_SERVICE_TYPE, $request) ?? 'general');
        $country = strtolower($this->arrayManager->get(static::REST_PATH_COUNTRY, $request) ?? '');

        $responsePath = sprintf(static::REST_RESPONSE_FILE_PATTERN, $moduleDir, $type, $country);

        if (!$this->fileIo->fileExists($responsePath)) {
            throw new NotFoundException(
                __('"%1" is not a valid mock response type for country "%2".', $type, $country)
            );
        }
        return $this->fileIo->read($responsePath);
    }

     /**
      * Load mock json response for a given request
      */
    public function loadForAuthRequest()
    {
        $moduleDir = $this->moduleDirectory->getDir('Magento_TestModuleFedex');
        $responsePath = sprintf(static::REST_AUTH_RESPONSE_FILE, $moduleDir);

        if (!$this->fileIo->fileExists($responsePath)) {
            throw new NotFoundException(
                __('No valid mock response found for Authentication.')
            );
        }
        return $this->fileIo->read($responsePath);
    }
}
