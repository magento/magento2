<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Paypal\Helper\Shortcut;

/**
 * Interface \Magento\Paypal\Helper\Shortcut\ValidatorInterface
 *
 * @since 2.0.0
 */
interface ValidatorInterface
{
    /**
     * Validates shortcut
     *
     * @param string $code
     * @param bool $isInCatalog
     * @return bool
     * @since 2.0.0
     */
    public function validate($code, $isInCatalog);
}
