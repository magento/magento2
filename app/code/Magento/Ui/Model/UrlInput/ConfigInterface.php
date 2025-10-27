<?php
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Ui\Model\UrlInput;

/**
 * Config interface for url link types
 *
 * @api
 */
interface ConfigInterface
{
    /**
     * Returns config for url link type
     *
     * @return array
     */
    public function getConfig(): array;
}
