<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
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
    public function getInitializedVector();

    /**
     * @param string $url
     */
    public function setUrl($url);

    /**
     * @param string $initializedVector
     */
    public function setInitializedVector($initializedVector);
}
