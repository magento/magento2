<?php
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
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
     * Retrieve url
     *
     * @return string
     */
    public function getUrl();

    /**
     * Retrieve initialization vector
     *
     * @return string
     */
    public function getInitializationVector();
}
