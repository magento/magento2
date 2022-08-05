<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\App;

/**
 * Request safety check. Can be used to identify if current application request is safe (does not modify state) or not.
 *
 * @api
 * @since 100.0.2
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
