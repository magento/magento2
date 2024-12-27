<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
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
