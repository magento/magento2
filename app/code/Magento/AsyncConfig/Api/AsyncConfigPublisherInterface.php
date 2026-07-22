<?php
/**
 * Copyright 2022 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\AsyncConfig\Api;

use Magento\Framework\Exception\FileSystemException;

interface AsyncConfigPublisherInterface
{
    /**
     * Save Configuration Data
     *
     * @param array $configData
     * @return void
     * @throws FileSystemException
     */
    public function saveConfigData(array $configData);
}
