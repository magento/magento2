<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Config;

/**
 * Config validation state interface.
 *
 * @api
 * @since 2.0.0
 */
interface ValidationStateInterface
{
    /**
     * Retrieve current validation state
     *
     * @return boolean
     * @since 2.0.0
     */
    public function isValidationRequired();
}
