<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
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
