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
 * @since 2.2.0
 */
interface LinkInterface
{
    /**
     * @return string
     * @since 2.2.0
     */
    public function getUrl();

    /**
     * @return string
     * @since 2.2.0
     */
    public function getInitializationVector();
}
