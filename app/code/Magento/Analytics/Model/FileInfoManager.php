<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\Model;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\FlagManager;

/**
 * Manage saving and loading FileInfo object.
 * @since 2.2.0
 */
class FileInfoManager
{
    /**
     * @var FlagManager
     * @since 2.2.0
     */
    private $flagManager;

    /**
     * @var FileInfoFactory
     * @since 2.2.0
     */
    private $fileInfoFactory;

    /**
     * Flag code for a stored FileInfo object.
     *
     * @var string
     * @since 2.2.0
     */
    private $flagCode = 'analytics_file_info';

    /**
     * Parameters which have to be saved into encoded form.
     *
     * @var array
     * @since 2.2.0
     */
    private $encodedParameters = [
        'initializationVector'
    ];

    /**
     * @param FlagManager $flagManager
     * @param FileInfoFactory $fileInfoFactory
     * @since 2.2.0
     */
    public function __construct(
        FlagManager $flagManager,
        FileInfoFactory $fileInfoFactory
    ) {
        $this->flagManager = $flagManager;
        $this->fileInfoFactory = $fileInfoFactory;
    }

    /**
     * Save FileInfo object.
     *
     * @param FileInfo $fileInfo
     * @return bool
     * @throws LocalizedException
     * @since 2.2.0
     */
    public function save(FileInfo $fileInfo)
    {
        $parameters = [];
        $parameters['initializationVector'] = $fileInfo->getInitializationVector();
        $parameters['path'] = $fileInfo->getPath();

        $emptyParameters = array_diff($parameters, array_filter($parameters));
        if ($emptyParameters) {
            throw new LocalizedException(
                __('These arguments can\'t be empty "%1"', implode(', ', array_keys($emptyParameters)))
            );
        }

        foreach ($this->encodedParameters as $encodedParameter) {
            $parameters[$encodedParameter] = $this->encodeValue($parameters[$encodedParameter]);
        }

        $this->flagManager->saveFlag($this->flagCode, $parameters);

        return true;
    }

    /**
     * Load FileInfo object.
     *
     * @return FileInfo
     * @since 2.2.0
     */
    public function load()
    {
        $parameters = $this->flagManager->getFlagData($this->flagCode) ?: [];

        $encodedParameters = array_intersect($this->encodedParameters, array_keys($parameters));
        foreach ($encodedParameters as $encodedParameter) {
            $parameters[$encodedParameter] = $this->decodeValue($parameters[$encodedParameter]);
        }

        $fileInfo = $this->fileInfoFactory->create($parameters);

        return $fileInfo;
    }

    /**
     * Encode value.
     *
     * @param string $value
     * @return string
     * @since 2.2.0
     */
    private function encodeValue($value)
    {
        return base64_encode($value);
    }

    /**
     * Decode value.
     *
     * @param string $value
     * @return string
     * @since 2.2.0
     */
    private function decodeValue($value)
    {
        return base64_decode($value);
    }
}
