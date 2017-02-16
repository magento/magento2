<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Analytics\Api\Data;

/**
 * Interface LinkInterface
 *
 * Represents link with collected data and iv for decryption.
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
    public function getIV();
}
