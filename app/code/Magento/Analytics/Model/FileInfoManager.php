<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\Model;

use Magento\Framework\Exception\LocalizedException;

class FileInfoManager
{
    /**
     * @var FlagManager
     */
    private $flagManager;

    /**
     * @var FileInfoFactory
     */
    private $fileInfoFactory;

    /**
     * @var string
     */
    private $flagCode = 'analytics_file_info';

    /**
     * @var array
     */
    private $encodedParameters = [
        'initializationVector'
    ];

    /**
     * @param FlagManager $flagManager
     * @param FileInfoFactory $fileInfoFactory
     */
    public function __construct(
        FlagManager $flagManager,
        FileInfoFactory $fileInfoFactory
    ) {
        $this->flagManager = $flagManager;
        $this->fileInfoFactory = $fileInfoFactory;
    }

    /**
     * @param FileInfo $fileInfo
     * @return bool
     * @throws LocalizedException
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
            if (isset($parameters[$encodedParameter])) {
                $parameters[$encodedParameter] = $this->encodeValue($parameters[$encodedParameter]);
            }
        }

        $this->flagManager->saveFlag($this->flagCode, $parameters);

        return true;
    }

    /**
     * @param string $value
     * @return string
     */
    private function encodeValue($value)
    {
        return base64_encode($value);
    }

    /**
     * @return FileInfo
     */
    public function load()
    {
        $parameters = $this->flagManager->getFlagData($this->flagCode);
        $fileInfo = $this->fileInfoFactory->create();

        $encodedParameters = array_intersect($this->encodedParameters, array_keys($parameters));
        foreach ($encodedParameters as $encodedParameter) {
            $parameters[$encodedParameter] = $this->decodeValue($parameters[$encodedParameter]);
        }

        if ($parameters) {
            foreach ($parameters as $parameter => $value) {
                call_user_func([$fileInfo, 'set' . $parameter], $value);
            }
        }

        return $fileInfo;
    }

    /**
     * @param string $value
     * @return string
     */
    private function decodeValue($value)
    {
        return base64_decode($value);
    }
}
