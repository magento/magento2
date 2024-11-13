<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Analytics\Api\Data;

/**
 * Represents link with collected data and initialized vector for decryption.
 *
 * @api
 */
interface LinkInterface
{
    /**
     * @return string
     */
    public function getUrl();

    /**
     * @return string
     */
    public function getInitializationVector();
}
