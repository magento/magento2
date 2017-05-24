<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\Api\Data;

/**
 * Interface LinkInterface
 *
 * Represents link with collected data and initialized vector for decryption.
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
