<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Paypal\Helper\Shortcut;

interface ValidatorInterface
{
    /**
     * Validates shortcut
     *
     * @param string $code
     * @param bool $isInCatalog
     * @return bool
     */
    public function validate($code, $isInCatalog);
}
