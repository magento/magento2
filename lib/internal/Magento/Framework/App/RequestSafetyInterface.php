<?php
/**
 * Application request
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\App;

/**
 * Interface RequestSafetyInterface
 *
 * @package Magento\Framework\App
 */
interface RequestSafetyInterface
{
    /**
     * Check that this is safe request
     *
     * @return bool
     */
    public function isSafeMethod();
}
